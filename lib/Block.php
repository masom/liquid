<?php

namespace Liquid;

use \Liquid\Liquid;
use \Liquid\Tags\ContinueTag;
use \Liquid\Tags\BreakTag;
use \Liquid\Variable;

class Block extends \Liquid\Tag {

    /** @var string */
    protected static $IsTag;
    /** @var string */
    protected static $IsVariable;
    /** @var string */
    protected static $FullToken;
    /** @var string */
    protected static $ContentOfVariable;

    /** @var boolean */
    protected static $init = false;

    /** @var boolean */
    protected $blank;

    /** @var array */
    protected $nodelist;

    /** @var array */
    protected $children;

    public static function init() {
        static::$IsTag = '/\A' . \Liquid\Liquid::TagStart . '/';
        static::$IsVariable = '/\A' . \Liquid\Liquid::VariableStart . '/';
        static::$FullToken = '/\A' . \Liquid\Liquid::TagStart . '\s*(\w+)\s*(.*)?' . \Liquid\Liquid::TagEnd . '\z/m';
        static::$ContentOfVariable = '/\A' . \Liquid\Liquid::VariableStart . '(.*)' . \Liquid\Liquid::VariableEnd . '\z/m';
    }

    /**
     * Was blank?
     */
    public function is_blank() {
        $this->blank || false;
    }

    public function __call($method, $arguments){
        if ($method === 'parse') {
            return $this->_parse($arguments[0]);
        }

        throw \BadMethodCallException();
    }

    public function _parse($tokens) {

        $this->blank = true;

        $this->nodelist = array();

        $this->children = array();

        while( $token = array_shift($tokens) ) {

            $matches = null;
            switch(true) {
            case preg_match(static::$IsTag, $token, $matches):

                echo 'is tag';
                if (preg_match(static::$Fulltoken, $token, $matches)) {

                    # if we found the proper block delimiter just end parsing here and let the outer block
                    # proceed
                    #
                    if ($this->block_delimiter() == $matches[1]) {
                        $this->end_tag();
                        return;
                    }

                    $tags = Template::tags();

                    if (isset($tags[$matches[1]])) {
                        $tag = $tags[$matches[1]];
                        $new_tag = $tag->parse($matches[1], $matches[2], $tokens, $this->options);

                        if ($new_tag->is_blank()) {
                            $this->blank = true;
                        }
                        $this->nodelist[] = $new_tag;
                        $this->children[] = $new_tag;
                    } else {
                        $this->unknown_tag($matches[1], $matches[2], $tokens);
                    }
                } else {
                    throw new \Liquid\Exceptions\SyntaxError("Tag '{$token}' was not properly terminated with regexp: {$tag_end}");
                }
                break;
            case preg_match(static::$IsVariable, $token, $matches):
                $new_var = $this->create_variable($token);
                $this->nodelist[] = $new_var;
                $this->children[] = $new_var;
                $this->blank = false;
                break;
            case empty($token):
                break;
            default:
                $this->nodelist[] = $token;
                if (preg_match('/\A\s*\z/', $token)) {
                    $this->blank = true;
                }
            }
        }

        $this->assert_missing_delimitation();
    }

    public function unknown_tag($tag, $params, $tokens) {
        $block_name = $this->block_name();
        if ($tag === 'else') {
            $msg = "{$block_name} tag does not expect else tag";
        } elseif ($tag === 'end') {
            $block_delimiter = $this->block_delimiter();
            $msg = "'end' is not a valid delimiter for {$block_name} tags. use {$block_delimiter}";
        } else {
            $msg = "Unknown tag '{$tag}'";
        }
        throw new \Liquid\Exceptions\SyntaxError($msg);
    }

    public function block_delimiter() {
        return 'end' . $this->block_name();
    }

    public function block_name() {
        return $this->tag_name;
    }

    public function create_variable($token) {
        $matches = null;
        preg_match_all(static::$ContentOfVariable, $token, $matches);
        foreach($matches as $match) {
            return new Variable(reset($match), $this->options); 
        }

        $tag_end = Liquid::VariableEnd;
        throw new \Liquid\Exceptions\SyntaxError("Variable '{$token}' was not properly terminated with regexp: {$tag_end}");
    }

    public function render($context) {
        $this->render_all($this->nodelist, $context);
    }

    protected function assert_missing_delimitation() {
        throw new \Liquid\Exceptions\SyntaxError("`{$this->block_name()}` tag was never closed.");
    }

    protected function render_all($items, $context) {
        $output = array();

        $context->resource_limits['render_length_current'] = 0;
        $context->resource_limits['render_score_current'] += count($items);

        foreach($items as $token) {
            if ($context->has_interrupt()) {
                break;
            }

            try {
                if ($token instanceof ContinueTag || $token instanceof BreakTag) {
                    $context->push_interrupt($token->interrupt());
                    break;
                }

                $token_output = method_exists($token, 'render') ? $token->render($context) : $token;

                $context->increment_used_resources('render_length_current', $token_output);

                if ($context->has_resource_limits_reached())
                {
                    $context->resource_limits['reached'] = true;
                    throw new \Liquid\Exceptions\MemoryError("Memory limit exceeded.");
                }

                if ( !($token instanceof Block) && !$token->is_blank()) {
                    $output[] = $token_output;
                }

            } catch(\Liquid\Exceptions\MemoryError $e) {
                throw $e;
            } catch(\Liquid\Exceptions\LiquidException $e) {
                $output[] = $context->handle_error($e);
            }
        }
        return implode(null, $output);
    }
}

Block::init();

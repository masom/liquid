<?php

namespace Liquid;

use \Liquid\Liquid;
use \Liquid\Tags\ContinueTag;
use \Liquid\Tags\BreakTag;
use \Liquid\Utils\Nodes;
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

    /** @var Nodes */
    protected $nodelist;

    /** @var array */
    protected $children;

    /** @var array */
    protected $warnings;

    public static function init() {
        static::$IsTag = '/\A' . \Liquid\Liquid::TagStart . '/';
        static::$IsVariable = '/\A' . \Liquid\Liquid::VariableStart . '/';
        static::$FullToken = '/\A' . \Liquid\Liquid::TagStart . '\s*(\w+)\s*(.*)?' . \Liquid\Liquid::TagEnd . '\z/s';
        static::$ContentOfVariable = '/\A' . \Liquid\Liquid::VariableStart . '(.*)' . \Liquid\Liquid::VariableEnd . '\z/s';
    }

    public function __call($method, $arguments){
        if ($method === 'parse') {
            return $this->_parse($arguments[0]);
        }

        throw new \BadMethodCallException("Method `" . __CLASS__ . "::{$method}` is undefined.");
    }

    /**
     * @param \Liquid\Utils\Tokens $tokens
     *
     * @throws Exceptions\SyntaxError
     */
    public function _parse($tokens) {
        $this->blank = true;

        $this->nodelist = new Nodes();

        $this->children = array();

        while( $token = $tokens->shift() ) {
            switch(true) {
            case preg_match(static::$IsTag, $token, $matches):

                if (preg_match(static::$FullToken, $token, $matches)) {

                    # if we found the proper block delimiter just end parsing here and let the outer block
                    # proceed
                    #
                    if ($this->block_delimiter() == $matches[1]) {
                        $this->end_tag();
                        return;
                    }

                    $tags = Template::tags();

                    # fetch the tag from registered blocks.
                    if (isset($tags[$matches[1]]) && $tag = $tags[$matches[1]]) {
                        /** @var \Liquid\Tag $new_tag */
                        $new_tag = $tag::parse($matches[1], $matches[2], $tokens, $this->options);

                        if (!$new_tag->is_blank()) {
                            $this->blank = false;
                        }

                        $this->nodelist[] = $new_tag;
                        $this->children[] = $new_tag;
                    } else {
                        $this->unknown_tag($matches[1], $matches[2], $tokens);
                    }
                } else {
                    $end_tag = Liquid::VariableEnd;
                    throw new \Liquid\Exceptions\SyntaxError("Tag '{$token}' was not properly terminated with regexp: {$end_tag}");
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

    /**
     * @return string
     */
    public function end_tag() {
    }

    /**
     * @return array
     */
    public function warnings() {
        $all_warnings = $this->warnings ?: array();

        if ($this->children) {
            foreach($this->children as $node) {
                if (!method_exists($node, 'warnings')) {
                    continue;
                }

                $all_warnings = array_merge($all_warnings, $node->warnings());
            }
        }

        return $all_warnings;
    }

    /**
     * @param $tag
     * @param $params
     * @param $tokens
     *
     * @throws Exceptions\SyntaxError
     */
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

    /**
     * @return string
     */
    public function block_delimiter() {
        return 'end' . $this->block_name();
    }

    /**
     * @return string
     */
    public function block_name() {
        return $this->tag_name;
    }

    /**
     * @param $token
     *
     * @return Variable
     * @throws Exceptions\SyntaxError
     */
    public function create_variable($token) {
        $matches = null;

        preg_match_all(static::$ContentOfVariable, $token, $matches);

        foreach($matches[1] as $match) {
            return new Variable($match, $this->options);
        }

        $tag_end = Liquid::VariableEnd;
        throw new \Liquid\Exceptions\SyntaxError("Variable '{$token}' was not properly terminated with regexp: {$tag_end}");
    }

    /**
     * @param Context $context
     *
     * @return string
     */
    public function render(&$context) {
        return $this->render_all($this->nodelist, $context);
    }

    /**
     * @throws Exceptions\SyntaxError
     */
    protected function assert_missing_delimitation() {
        throw new \Liquid\Exceptions\SyntaxError("`{$this->block_name()}` tag was never closed.");
    }

    /**
     * @param Nodes $items
     * @param Context $context
     *
     * @return string
     * @throws \Exception
     * @throws Exceptions\MemoryError
     */
    protected function render_all($items, &$context) {
        $output = array();

        $limits = $context->resource_limits();
        $limits['render_length_current'] = 0;
        $limits['render_score_current'] += count($items);

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

                if ($context->is_resource_limits_reached())
                {
                    $limits['reached'] = true;
                    throw new \Liquid\Exceptions\MemoryError("Memory limits exceeded");
                }

                if (is_object($token)) {
                    if (!($token instanceof Block) && method_exists($token, 'is_blank')) {
                        if (!$token->is_blank()) {
                            $output[] = $token_output;
                        }
                    } else {
                        $output[] = $token_output;
                    }
                } else {
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

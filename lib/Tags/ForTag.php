<?php

namespace Liquid\Tags;

use \Liquid\Parser;
use \Liquid\Liquid;
use \Liquid\Utils;
use \Liquid\Utils\Nodes;

class ForTag extends \Liquid\Block {

    protected static $Syntax;

    /** @var array */
    protected $for_block;

    /** @var array */
    protected $else_block;

    /** @var string */
    protected $collection_name;

    /** @var boolean */
    protected $reversed;

    public static function init() {
        static::$Syntax = '/\A(' . Liquid::VariableSegment .'+)\s+in\s+(' . Liquid::$PART_QuotedFragment .'+)\s*(reversed)?/';
    }

    public function __construct($tag_name, $markup, $options) {
        parent::__construct($tag_name, $markup, $options);

        $this->parse_with_selected_parser($markup);
        $this->for_block = new Nodes(); 
        $this->nodelist =& $this->for_block;
    }

    public function nodelist() {
        if ($this->else_block) {
            return $this->for_block->merge($this->else_block);
        } else {
            return $this->for_block;
        }
    }

    public function unknown_tag($tag, $markup, $tokens) {
        if ($tag !== 'else') {
            return parent::unknown_tag($tag, $markup, $tokens);
        }
        $this->else_block = new Nodes();
        $this->nodelist =& $this->else_block;
    }

    public function render($context) {
        $registers = $context->registers();

        $collection = $context[$this->collection_name];

        if (!is_array($collection)) { //todo figure if we can iterate.
            return $this->render_else($context);
        }

        if (isset($this->attributes['offset'])) {
            if ($this->attributes['offset'] == 'continue') {
                $from = (int) $registers['for'][$this->name];
            } else {
                $from = (int) $context[$this->attributes['offset']];
            }
        } else {
            $from = null;
        }

        $limit = isset($this->attributes['limit']) ? $context[$this->attributes['limit']] : null;
        $to = $limit ? (int) $limit + $from : null;

        $segment = Utils::slice_collection($collection, $from, $to);

        if (empty($segment)) {
            return $this->render_else($context);
        }

        if ($this->reversed) {
            array_flip($segment);
        }

        $result = '';

        $length = count($segment);

        $registers['for'][$this->name] = $from + $length;

        $self = $this;
        $result = null;
        $variable_name = &$this->variable_name;
        $name =& $this->name;
        $for_block =& $this->for_block;


        $context->stack(function($context) use ($self, &$result, &$for_block, &$segment, &$variable_name, &$name, $length) {
            foreach($segment as $key => $item) {
                $context[$variable_name] = $item;
                $context['forloop'] = array(
                    'name' => $name,
                    'length' => $length,
                    'index' => $key + 1,
                    'index0' => $key,
                    'rindex' => $length - $key,
                    'rindex0' => $length - $key - 1,
                    'first' => ($key==0),
                    'last' => ($key == $length - 1)
                );

                $result .= $self->render_all($for_block, $context);

                if ($context->has_interrupt()) {
                    $interrupt = $context->pop_interrupt();
                    if ($interrupt instanceof BreakInterrupt) {
                        break;
                    }
                    if ($interrupt instanceof ContinueInterrupt) {
                        continue;
                    }
                }
            }
        });

        return $result;
    }

    public function lax_parse(&$markup) {
        if (preg_match(static::$Syntax, $markup, $matches)) {
            $this->variable_name = $matches[1];
            $this->collection_name = $matches[2];
            $this->name = $matches[1] . '-' . $matches[2];

            $this->reversed = isset($matches[3]);

            $this->attributes = array();

            if(preg_match_all(Liquid::$TagAttributes, $markup, $matches)) {
                foreach($matches as $key => $value) {
                    $this->attributes[$key] = $value;
                }
            }
        } else {
            throw new \Liquid\Exceptions\SyntaxError("Syntax Error in 'for loop' - Valid syntax: for [item] in [collection]");
        }
    }

    public function strict_parse(&$markup) {
        $p = new Parser($markup);

        $this->variable_name = $p->consume('id');
        if (!$p->try_id('in')) {
            throw new \Liquid\Exceptions\SyntaxError("For loops require an 'in' clause");
        }

        $this->collection_name = $p->expression();

        $this->name = "{$this->variable_name}-{$this->collection_name}";
        $this->reversed = $p->try_id('reversed');

        $this->attributes = array();

        while($p->look('id') && $p->look('colon', 1)) {
            $attribute = $p->try_id('limit');

            if (!$attribute || $p->try_id('offset')) {
                throw new \Liquid\Exceptions\SyntaxError("Invalid attribute in for loop. Valid attributes are limit and offset");
            }

            $p->consume();

            $val = $p->expression();

            $this->attributes[$attribute] = $val;
        }

        $p->consume('end_of_string');
    }

    private function render_else(&$context) {
        return $this->else_block ? array($this->render_all($this->else_block, $context)) : '';
    }

    private function is_iterable(&$collection) {
        return (is_array($collection) || $collection instanceof \ArrayAccess) || Utils::is_non_blank_string($collection);
    }
}

ForTag::init();

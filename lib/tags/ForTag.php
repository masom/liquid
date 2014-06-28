<?php

namespace Liquid\Tags;

use \Liquid\Liquid;
use \Liquid\Utils;

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
        static::$Syntax = '/\A(' . Liquid::VariableSegment .'+)\s+in\s+(' . Liquid::QuotedFragment .'+)\s*(reversed)?/o';
    }

    public function __construct($tag_name, $markup, $options) {
        parent::__construct($tag_name, $markup, $options);

        $this->parse_with_selected_parser($markup);

        $this->nodelist = array();
        $this->for_block = array();
    }

    public function nodelist() {
        if ($this->else_block) {
            return array_merge($this->for_block, $this->else_block);
        } else {
            return $for_block;
        }
    }

    public function unknown_tag($tag, $markup, $tokens) {
        if ($tag == 'else') {
            return parent::unknown_tag($tag, $markup, $tokens);
        }

        $this->nodelist = array();
        $this->else_block = array();
    }

    public function render($context) {
        $registers = $context->registers();

        $registers['for'] = $registers['for'] ?: array();

        $collection = $context[$this->collection_name];

        if (!is_array($collection)) { //todo figure if we can iterate.
            return $this->render_else($context);
        }

        if ($this->attributes['offset'] == 'continue') {
            $from = (int) $registers['for'][$this->name];
        } else {
            $from = (int) $context[$this->attributes['offset']];
        }

        $limit = $context[$this->attributes['limit']];
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

        $context->stack(function($context) use ($self, &$for_block, &$segment, &$variable_name, &$name, $length, $index) {
            foreach($segment as $key => $item) {
                $context[$variable_name] = $item;
                $context['forloop'] = array(
                    'name' => $name,
                    'length' => $length,
                    'index' => $index + 1,
                    'index0' => $index,
                    'rindex' => $length - $index,
                    'rindex0' => $length - $index - 1,
                    'first' => ($index==0),
                    'last' => ($index == $length - 1)
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

    public function strict_parse($markup) {
        $p = new Parser($markup);

        $this->variable_name = $p->consume('id');
        if (!$p->has_id('in')) {
            throw new \Liquid\Exceptions\SyntaxError("For loops require an 'in' clause");
        }

        $this->collection_name = $p->expression();

        $this->name = "{$this->variable_name}-{$this->collection_name}";
        $this->reversed = $p->has_id('reversed');

        $this->attributes = array();

        while($p->look('id') && $p->look('colon', 1)) {
            $attribute = $p->has_id('limit');

            if (!$attribute || $p->has_id('offset')) {
                throw new \Liquid\Exceptions\SyntaxError("Invalid attribute in for loop. Valid attributes are limit and offset");
            }

            $p->consume();

            $val = $p->expression();

            $this->attributes[$attribute] = $val;
        }

        $p->consume('end_of_string');
    }

    private function render_else($context) {
        return $this->else_block ? array($this->render_all($this->else_block, $context)) : '';
    }

    private function is_iterable($collection) {
        return (is_array($collection) || $collection instanceof \ArrayAccess) || Utils::is_non_blank_string($collection);
    }
}

ForTag::init();

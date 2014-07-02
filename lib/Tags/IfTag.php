<?php

namespace Liquid;

use \Liquid\Liquid;
use \Liquid\Parser;
use \Liquid\Utils\Arrays;

class IfTag extends \Liquid\Block {
    protected static $Syntax;
    protected static $ExpressionsAndOperators;
    protected static $BOOLEAN_OPERATORS = array('and', 'or');

    protected $blocks = array();

    public static function init() {
        static::$Syntax = '/(' . Liquid::QuotedFragment .')\s*([=!<>a-z_]+)?\s*(' . Liquid::QuotedFragment . ')?/o';
        static::$ExpressionsAndOperators = '/(?:\b(?:\s?and\s?|\s?or\s?)\b|(?:\s*(?!\b(?:\s?and\s?|\s?or\s?)\b)(?:' . Liquid::QuotedFragment . '|\S+)\s*)+)/o';
    }
    public function __construct($tag_name, $markup, $options) {
        parent::__construct($tag_name, $markup, $options);

        $this->push_block('if', $markup);
    }

    public function nodelist() {
        $blocks = array();

        foreach($this->blocks as $block) {
            $blocks[] = $block->attachment();
        }

        return Arrays::flatten($blocks);
    }

    public function unknown_tag($tag, $markup, $tokens) {
        if ($tag === 'elseif' || $tag === 'else') {
            $this->push_block($tag, $markup);
        } else {
            parent::unknown_tag($tag, $markup, $tokens);
        }
    }

    public function render($context) {
        $blocks =& $this->blocks;
        $result = '';
        $self = $this;
        $context->stack(function($context) use ($self, &$blocks, &$result) {
            foreach($blocks as $block) {
                if($block->evaluate($context)) {
                    $result = $self->render_all($block->attachment, $context);
                    return;
                }
            }
        });

        return $result;
    }

    private function push_block($tag, $markup) {
        if ($tag === 'else') {
            $block = new ElseCondition();
        } else {
            $block = $this->parse_with_selected_parser($markup);
        }

        $this->blocks[] = $block;

        $this->nodelist = $block->attach(array());
    }

    public function strict_parse($markup) {
        $p = new Parser($markup);

        $condition = $this->parse_comparison($p);

        while($op = ($p->try_id('and') || $p->try_id('or'))) {
            $new_cond = $this->parse_comparison($p);
            $new_cond->{$op}($condition);
            $condition = $new_cond;
        }

        $p->consume('end_of_string');

        return $condition;
    }

    public function parse_comparison($p) {
        $a = $p->expression();

        if ($op = $p->try_consume('comparison')) {
            $b = $p->expression();
            return new Condition($a, $op, $b);
        } else {
            return new Condition($a);
        }
    }
}

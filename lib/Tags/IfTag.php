<?php

namespace Liquid\Tags;

use \Liquid\Condition;
use \Liquid\ElseCondition;
use \Liquid\Liquid;
use \Liquid\Parser;
use \Liquid\Utils\Arrays;
use \Liquid\Utils\Nodes;

class IfTag extends \Liquid\Block {
    protected static $Syntax;
    protected static $ExpressionsAndOperators;
    protected static $BOOLEAN_OPERATORS = array('and'=>'and', 'or'=>'or');

    protected $blocks = array();

    public static function init() {
        static::$Syntax = '/(' . Liquid::$PART_QuotedFragment .')\s*([=!<>a-z_]+)?\s*(' . Liquid::$PART_QuotedFragment . ')?/';
        static::$ExpressionsAndOperators = '/(?:\b(?:\s?and\s?|\s?or\s?)\b|(?:\s*(?!\b(?:\s?and\s?|\s?or\s?)\b)(?:' . Liquid::$PART_QuotedFragment . '|\S+)\s*)+)/';
    }
    public function __construct($tag_name, &$markup, &$options) {
        parent::__construct($tag_name, $markup, $options);

        $this->push_block('if', $markup);
    }

    public function nodelist() {
        $blocks = array();

        foreach($this->blocks as $block) {
            $blocks[] = $block->attachment()->nodes();
        }

        return Arrays::flatten($blocks);
    }

    public function unknown_tag($tag, $markup, $tokens) {
        if ($tag === 'elseif' || $tag === 'else') {
            return $this->push_block($tag, $markup);
        } else {
            return parent::unknown_tag($tag, $markup, $tokens);
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

    private function push_block($tag, &$markup) {
        if ($tag === 'else') {
            $block = new ElseCondition();
        } else {
            $block = $this->parse_with_selected_parser($markup);
        }

        $this->blocks[] = $block;

        $this->nodelist = $block->attach(new Nodes());

        return $this->nodelist;
    }

    public function lax_parse(&$markup) {
        preg_match_all(static::$ExpressionsAndOperators, $markup, $matches);

        $expressions = array_reverse($matches[0]);

        if (!preg_match(static::$Syntax, array_shift($expressions), $matches)) {
            throw new \Liquid\Exceptions\SyntaxError("Syntax Error in tag 'if' - Valid syntax: if [expression]");
        }

        $condition = new Condition($matches[1], isset($matches[2]) ? $matches[2] : null, isset($matches[3]) ? $matches[3] : null);

        while($expressions) {
            $operator = trim((string) array_shift($expressions));

            if (!preg_match(static::$Syntax, trim((string) array_shift($expressions)), $matches)) {
                throw new \Liquid\Exceptions\SyntaxError("Syntax Error in tag 'if' - Valid syntax: if [expression]");
            }

            $new_condition = new Condition($matches[1], isset($matches[2]) ? $matches[2] : null, isset($matches[3]) ? $matches[3] : null);

            if(!isset(static::$BOOLEAN_OPERATORS[$operator])) {
                throw new \Liquid\Exceptions\SyntaxError("Syntax Error in tag 'if' - Valid syntax: if [expression]");
            }

            $new_condition->{$operator}($condition);
            $condition = $new_condition;
        }

        return $condition;
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

    private function parse_comparison($p) {
        $a = $p->expression();

        if ($op = $p->try_consume('comparison')) {
            $b = $p->expression();
            return new Condition($a, $op, $b);
        } else {
            return new Condition($a);
        }
    }
}
IfTag::init();

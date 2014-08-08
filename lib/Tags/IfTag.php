<?php

namespace Liquid\Tags;

use \Liquid\Condition;
use \Liquid\ElseCondition;
use Liquid\Lexer;
use \Liquid\Liquid;
use \Liquid\Parser;
use \Liquid\Utils\Arrays;
use \Liquid\Utils\Nodes;

class IfTag extends \Liquid\Block
{
    protected static $Syntax;
    protected static $ExpressionsAndOperators;
    protected static $BOOLEAN_OPERATORS = array('and' => 'and', 'or' => 'or');

    /** @var \ArrayObject */
    protected $blocks;

    public static function init()
    {
        static::$Syntax = '/(' . Liquid::$PART_QuotedFragment . ')\s*([=!<>a-z_]+)?\s*(' . Liquid::$PART_QuotedFragment . ')?/';
        static::$ExpressionsAndOperators = '/(?:\b(?:\s?and\s?|\s?or\s?)\b|(?:\s*(?!\b(?:\s?and\s?|\s?or\s?)\b)(?:' . Liquid::$PART_QuotedFragment . '|\S+)\s*)+)/';
    }

    /**
     * @param string $tag_name
     * @param string $markup
     * @param array $options
     */
    public function __construct($tag_name, &$markup, &$options)
    {
        parent::__construct($tag_name, $markup, $options);

        $this->blocks = new \ArrayObject();

        $this->push_block('if', $markup);
    }

    /**
     * @return array|Nodes
     */
    public function nodelist()
    {
        $blocks = array();

        foreach ($this->blocks as $block) {
            /** @var \Liquid\Condition $block */
            $blocks[] = $block->attachment()->nodes();
        }

        return Arrays::flatten($blocks);
    }

    /**
     * @param $tag
     * @param $markup
     * @param $tokens
     */
    public function unknown_tag($tag, $markup, $tokens)
    {
        if ($tag === 'elsif' || $tag === 'else') {
            $this->push_block($tag, $markup);
        } else {
            parent::unknown_tag($tag, $markup, $tokens);
        }
    }

    /**
     * @param \Liquid\Context $context
     *
     * @return string
     */
    public function render(&$context)
    {
        $blocks =& $this->blocks;
        $result = '';
        $self = $this;

        $context->stack(function ($context) use ($self, &$blocks, &$result) {
            foreach ($blocks as $block) {
                if ($block->evaluate($context)) {
                    $result = $self->render_all($block->attachment(), $context);
                    return;
                }
            }
        });

        return $result;
    }

    /**
     * @param $tag
     * @param $markup
     *
     * @return Nodes
     */
    private function push_block($tag, &$markup)
    {
        if ($tag === 'else') {
            $block = new ElseCondition();
        } else {
            $block = $this->parse_with_selected_parser($markup);
        }

        $this->blocks[] = $block;


        $this->nodelist = $block->attach(new Nodes());

        return $this->nodelist;
    }

    /**
     * @param $markup
     *
     * @return Condition
     * @throws \Liquid\Exceptions\SyntaxError
     */
    public function lax_parse(&$markup)
    {
        preg_match_all(static::$ExpressionsAndOperators, $markup, $matches);

        $expressions = array_reverse($matches[0]);

        if (!preg_match(static::$Syntax, array_shift($expressions), $matches)) {
            throw new \Liquid\Exceptions\SyntaxError("Syntax Error in tag 'if' - Valid syntax: if [expression]");
        }

        $condition = new Condition($matches[1], isset($matches[2]) ? $matches[2] : null, isset($matches[3]) ? $matches[3] : null);

        //TODO Something is wrong here.
        while ($expressions) {
            $operator = trim((string)array_shift($expressions));

            if (!preg_match(static::$Syntax, trim((string)array_shift($expressions)), $matches)) {
                throw new \Liquid\Exceptions\SyntaxError("Syntax Error in tag 'if' - Valid syntax: if [expression]");
            }

            $new_condition = new Condition($matches[1], isset($matches[2]) ? $matches[2] : null, isset($matches[3]) ? $matches[3] : null);

            if (!isset(static::$BOOLEAN_OPERATORS[$operator])) {
                throw new \Liquid\Exceptions\SyntaxError("Syntax Error in tag 'if' - Valid syntax: if [expression]");
            }

            $new_condition->{$operator}($condition);
            $condition = $new_condition;
        }

        return $condition;
    }

    /**
     * @param string $markup
     *
     * @return Condition
     */
    public function strict_parse($markup)
    {
        $p = new Parser($markup);

        $condition = $this->parse_comparison($p);

        while ($op = $this->get_op($p)) {
            $new_cond = $this->parse_comparison($p);
            $new_cond->{$op}($condition);
            $condition = $new_cond;
        }

        $p->consume(Lexer::TOKEN_ENDOFSTRING);

        return $condition;
    }

    /**
     * @param Parser $p
     * @return bool|null
     */
    private function get_op($p)
    {
        if ($op = $p->try_id('and') || $op = $p->try_id('or')) {
            return $op;
        }
        return null;
    }

    /**
     * @param Parser $p
     *
     * @return Condition
     */
    private function parse_comparison($p)
    {
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

<?php

namespace Liquid\Tags;

use \Liquid\Liquid;
use \Liquid\Condition;
use \Liquid\ElseCondition;
use \Liquid\Utils\Arrays;
use \Liquid\Utils\Nodes;

class CaseTag extends \Liquid\Block {

    protected static $init = false;
    protected static $Syntax;
    protected static $WhenSyntax;


    protected $blocks = array();
    protected $left;

    public static function init() {
        static::$init = true;

        static::$Syntax = '/(' . Liquid::$PART_QuotedFragment . ')/';
        static::$WhenSyntax = '/(' . Liquid::$PART_QuotedFragment . ')(?:(?:\s+or\s+|\s*\,\s*)(' . Liquid::$PART_QuotedFragment . '.*))?/Ss';
    }

    /**
     * @param string $tag_name
     * @param string $markup
     * @param array  $options
     *
     * @throws \Liquid\Exceptions\SyntaxError
     */
    public function __construct($tag_name, $markup, $options) {
        parent::__construct($tag_name, $markup, $options);

        $matches = null;
        if (preg_match(static::$Syntax, $markup, $matches)) {
            $this->left = $matches[1];
        } else {
            throw new \Liquid\Exceptions\SyntaxError("Syntax Error in 'case' - Valid syntax: case [condition]");
        }
    }

    /**
     * @return array
     */
    public function nodelist() {
        $blocks = array();

        foreach($this->blocks as $block) { /** @var \Liquid\Condition $block */
            if ($attachment = $block->attachment()) {
                $blocks[] = $attachment->nodes();
            }
        }

        return Arrays::flatten($blocks);
    }

    /**
     * @param $tag
     * @param string $markup
     * @param \Liquid\Utils\Tokens $tokens
     */
    public function unknown_tag($tag, $markup, $tokens) {
        $this->nodelist = new Nodes();

        switch($tag) {
        case 'when':
            return $this->record_when_condition($markup);
        case 'else':
            return $this->record_else_condition($markup);
        default:
            return parent::unknown_tag($tag, $markup, $tokens);
        }
    }

    /**
     * @param \Liquid\Context $context
     * @return string
     */
    public function render(&$context) {

        $output = '';
        $blocks =& $this->blocks;
        $self = $this;
        $context->stack(function($context) use ($self, &$blocks, &$output) {
            $execute_else_block = true;

            foreach($blocks as $block) { /** @var \Liquid\Condition $block */
                if ($block->isElse()) {
                    if ($execute_else_block) {
                        $output = $self->render_all($block->attachment(), $context);
                        break;
                    }
                } elseif ($block->evaluate($context)) {
                    $execute_else_block = false;
                    $output .= $self->render_all($block->attachment(), $context);
                }
            }
        });
        return $output;
    }

    /**
     * @param $markup
     *
     * @throws \Liquid\Exceptions\SyntaxError
     */
    private function record_when_condition($markup) {
        while($markup) {
            $matches = null;
            if (!preg_match(static::$WhenSyntax, $markup, $matches)) {
                throw new \Liquid\Exceptions\SyntaxError("Syntax Error in tag 'case' - Valid when condition: {% when [condition] [or condition2...] %}");
            }

            $markup = isset($matches[2]) ? $matches[2] : null;

            $block = new Condition($this->left, '==', $matches[1]);
            $block->attach($this->nodelist);
            $this->blocks[] = $block;
        }
    }

    /**
     * @param $markup
     *
     * @throws \Liquid\Exceptions\SyntaxError
     */
    private function record_else_condition($markup) {
        $markup = trim($markup);
        if (!empty($markup)) {
            throw new \Liquid\Exceptions\SyntaxError("Syntax Error in tag 'case' - Valid else condition: {% else %} (no parameters) ");
        }

        $block = new ElseCondition();
        $block->attach($this->nodelist);
        $this->blocks[] = $block;
    }
}

CaseTag::init();

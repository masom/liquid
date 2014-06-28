<?php

namespace Liquid\Tags;

use \Liquid\Liquid;


class Cycle extends \Liquid\Tag {
    protected static $SimpleSyntax;
    protected static $NamedSyntax;

    protected $variables;
    protected $name;
    
    public static function init(){ 
        static::$SimpleSyntax = '/\A' . Liquid::QuotedFragment . '+/o';
        static::$NamedSyntax = '/\A(' . Liquid::QuotedFragment . ')\s*\:\s*(.*)/om';
    }

    public function __construct($tag_name, $markup, $options) {
        parent::__construct($tag_name, $markup, $options);

        $matches = null;
        switch(true) {
        case preg_match(static::$SimpleSyntax, $markup, $matches):
            $this->variables = $this->variables_from_string($matches[2]);
            $this->name = $matches[1];
            break;
        case preg_match(static::$NamedSyntax, $markup, $matches):
            $this->variables = $this->variables_from_string($markup);
            $this->name = "'{$this->variables}'";
            break;
        default:
            throw new \Liquid\Exceptions\SyntaxError("Syntax Error in 'cycle' - Valid syntax: cycle [name :] var [, var2, var3 ...]");
        }
    }

    public function render($context) {
        $registers = $context->registers();
        $registers['cycle'] = $registers['cycle'] ?: array();

        $self = $this;

        $name =& $this->name;
        $variables =& $this->variables;
        $result = null;

        $context->stack(function($context) use ($registers, &$name, &$variables, &$result){
            $key = $context[$name];

            $iteration = $registers['cycle'][$key];

            $result = $context[$variables[$iteration]];
            $iteration++;
            if ($iteration >= count($variables)) {
                $iteration = 0;
            }

            $registers['cycle'][$key] = $iteration;
        });

        return $result;
    }

    public function is_blank() {
        return false;
    }

    private function variables_from_string($markup) {
        $variables = explode(',', $markup);

        foreach($variables as &$var) {
            $matches = null;
            preg_match('/\s*(' . Liquid::QuotedFragment . ')\s*/o', $var, $matches);

            $var = isset($matches[1]) && $matches[1] ? $matches[1] : null;
        }

        return array_filter($variables);
    }
}

Cycle::init();

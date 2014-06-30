<?php

namespace Liquid;

use \Liquid\Lexer;
use \Liquid\Liquid;
use \Liquid\Parser;

class Variable {

    protected $markup;
    protected $name;
    protected $options;
    protected $filters;

    protected static $FilterParser;
    const EasyParse = '/\A *(\w+(?:\.\w+)*) *\z/';

    public static function init() {
        static::$FilterParser = '/(?:' . Liquid::FilterSeparator . '|(?:\s*(?:' . Liquid::$QuotedFragment . '|' . Liquid::ArgumentSeparator . ')\s*)+)/o';
    }

    public function __construct($markup, array $options = array()) {
        $this->markup = $markup;
        $this->options = $options;

        $this->strict_parse($markup);
    }

    public function name() {
        return $this->name;
    }

    public function options(){ 
        return $this->options();
    }
    
    public function filters() {
        return $this->filters;
    }

    public function strict_parse($markup) {

        $this->filters = array();


        $matches = null;
        if (preg_match(static::EasyParse, $markup, $matches)) {
            $this->name = $matches[1];
            return;
        }

        $p = new Parser($markup);

        try{
            $this->name = $p->look(Lexer::TOKEN_PIPE) ? '' : $p->expression();

            while($p->try_consume(Lexer::TOKEN_PIPE)) {
                $filtername = $p->consume(Lexer::TOKEN_ID);
                $filterargs = $p->try_consume(Lexer::TOKEN_COLON) ? $this->parse_filterargs($p) : array();
                $this->filters[] = array($filtername, $filterargs);
            }

            $p->consume(Lexer::TOKEN_ENDOFSTRING);
        } catch(\Liquid\Exceptions\SyntaxError $e) {
            $e->setMessage($e->getMessage() . ' in "{{' . $markup . '}}"');
            throw $e;
        }
    }

    public function parse_filterargs($p) {
        // first argument
        $filterargs = array($p->argument());
        // followed by comma separated others
        while($p->try_consume(Lexer::TOKEN_COMMA)) {
            $filterargs[] = $p->argument();
        }
        return $filterargs;
    }

    public function render($context) {
        if ($this->name == null) {
            return '';
        }

        return array_reduce($this->filters, function($output, $filter) use ($context) {
            $filterargs = array();
            $keyword_args = array();
            foreach($filter[1] as $a) {
                $matches = null;
                if (preg_match('/\A' . Liquid::TagAttributes . '\z/o', $a, $matches)) {
                    $keyword_args[$matches[1]] = $context[$matches[2]];
                } else {
                    $filterargs[] = $context[$a];
                }
            }

            if ($keyword_args){
                $filterargs[] = $keyword_args;
            }

            try {
                $output = $context->invoke($filter[0], $output, $filterargs);
            } catch(\Liquid\FilterNotFound $e) {
                $markup = trim($markup);
                throw new FilterNotFound("Error - filter '{$filter[0]}' in '{$markup}' could not be found.");
            }
        }, $context[$this->name]);
    }
}
Variable::init();

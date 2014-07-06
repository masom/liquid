<?php

namespace Liquid;

use \Liquid\Lexer;
use \Liquid\Liquid;
use \Liquid\Parser;
use \Liquid\Utils\Arrays;
use \Liquid\Template;


class Variable {

    protected $warnings;
    protected $markup;
    protected $name;
    protected $options;
    protected $filters;

    protected static $FilterParser;

    protected static $LAX_Parse;
    protected static $LAX_FilterParser;
    protected static $LAX_FilterArgsParser;

    const EasyParse = '/\A *(\w+(?:\.\w+)*) *\z/';

    public static function init() {
        static::$FilterParser = '/(?:' . Liquid::FilterSeparator . '|(?:\s*(?:' . Liquid::$PART_QuotedFragment . '|' . Liquid::ArgumentSeparator . ')\s*)+)/';

        static::$LAX_Parse = '/\s*(' . Liquid::$PART_QuotedFragment . ')(.*)/m';
        static::$LAX_FilterParser = '/'. Liquid::FilterSeparator .'\s*(.*)/m';
        static::$LAX_FilterArgsParser = '/(?:' . Liquid::FilterArgumentSeparator . '|' . Liquid::ArgumentSeparator . ')\s*((?:\w+\s*\:\s*)?' . Liquid::$PART_QuotedFragment .')/';
    }

    public function __construct($markup, array $options = array()) {
        $this->markup = $markup;
        $this->options = $options + array('error_mode' => Template::error_mode());

        switch($this->options['error_mode']) {
        case Liquid::ERROR_MODE_STRICT:
            $this->strict_parse($markup);
            break;

        case Liquid::ERROR_MODE_LAX:
            $this->lax_parse($markup);
            break;

        case Liquid::ERROR_MODE_WARN:
            try{
                $this->strict_parse($markup);
            } catch(\Liquid\Exceptions\SyntaxError $e) {
                $this->warnings[] = $e;
                $this->lax_parse($markup);
            }
            break;
        }
    }

    public function warnings() {
        return $this->warnings;
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

    public function lax_parse($markup) {
        $this->filters = array();

        $matches = null;
        if (!preg_match(static::$LAX_Parse, $markup, $matches)) {
            return;
        }
        
        $this->name = $matches[1];

        $secondMatches = null;
        if (!preg_match(static::$LAX_FilterParser, $matches[2], $secondMatches)) {
            return;
        }

        $filters = null;
        preg_match_all(static::$FilterParser, $secondMatches[1], $filters);

        foreach($filters[0] as $f) {
            $filterMatch = null;

            if (preg_match('/\s*(\w+)/', $f, $filterMatch)) {
                $filtername = $filterMatch[1];

                preg_match_all(static::$LAX_FilterArgsParser, $f, $filterargs);

                $filterargs = Arrays::flatten($filterargs[1]);
                $this->filters[] = array($filtername, $filterargs);
            }
        }
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
            return null;
        }

        $output = $context[$this->name];

        foreach($this->filters as $filter) {
            $filterargs = array();
            $keyword_args = array();
            foreach($filter[1] as $a) {
                $matches = null;
                if (preg_match('/\A' . Liquid::$PART_TagAttributes . '\z/', $a, $matches)) {
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
        }

        return $output;
    }
}
Variable::init();

<?php

namespace Liquid;

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
        static::$init = true;
        static::$FilterParser = '/(?:' . Liquid::FilterSeparator . '|(?:\s*(?:' . Liquid::QuotedFragment . '|' . Liquid::ArgumentSeparator . ')\s*)+)/o';
    }

    public function __construct($markup, array $options = array()) {
        if (!static::$init) {
            static::init();
        }

        $this->markup = $markup;
        $this->options = $options;

        $this->strict_parse($markup);
    }

    public function strict_parse($markup) {
        $matches = null;
        $this->filters = array();

        if (preg_match(static::EasyParse, $markup, $matches)) {
            $this->name = $matches[1];
            return;
        }

        $p = new Parser($markup);

        try{
        $this->name = $p->look('pipe') ? '' : $p->expression();
        while($p->tryConsume('id')) {
            $filtername = $p->consume('id');
            $filterargs = $p->tryConsume('colon') ? $this->parse_filterargs($p) : array();
            $this->filters[] = array($filtername, $filterargs);
        }

        $p->consume('end_of_string');
        } catch(\Liquid\Exceptions\SyntaxError $e) {
            $e->setMessage($e->getMessage() . ' in "{{' . $markup . '}}"');
            throw $e;
        }
    }

    public function parse_filterargs($p) {
        $filterargs = array($p->argument());
        while($p->tryConsume('comma')) {
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

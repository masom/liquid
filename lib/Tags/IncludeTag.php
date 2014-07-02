<?php

namespace Liquid\Tags;

use \Liquid\Liquid;
use \Liquid\Template;

class IncludeTag extends \Liquid\Tag {
    protected static $Syntax;

    /** @var string */
    protected $template_name;

    protected $attributes;

    public static function init() {
        static::$Syntax = '/(' . Liquid::QuotedFragment . '+)(\s+(?:with|for)\s+(' . Liquid::QuotedFragment . '+))?/o';
    }

    public function __construct($tag_name, $markup, $options) {
        parent::__construct($tag_name, $markup, $options);

        $matches = null;
        if (preg_match(static::$Syntax, $markup, $matches)) {
            $this->template_name = $matches[1];
            $this->variable_name = $matches[3];
            $this->attributes = array();

            preg_match_all(Liquid::TagAttributes, $markup, $matches);
            foreach($matches as $key => $value) {
                $this->attributes[$key] = $value;
            }
        } else {
            throw new \Liquid\Exceptions\SyntaxError("Error in tag 'include' - Valid syntax: include '[template]' (with|for) [object|collection]");
        }
    }

    public function parse($tokens) {
    }

    public function is_blank() {
        return false;
    }

    public function render($context) {
        $partial = $this->load_cached_partial($context);

        $variable = $context[$this->variable_name || substr($this->template_name, 1, strlen($this->template_name) -2 )];

        $attributes =& $this->attributes;

        $return = null;
        $context->stack(function($context) use (&$partial, &$variable, &$attributes, &$return) {
            foreach($attributes as $key => $value) {
                $context[$key] = $context[$value];
            }

            $context_variable_name = end(explode('/', substr($this->template_name, 1, strlen($this->template_name) -2 )));

            if (is_array($variable)) {
                $new = array();
                foreach($variable as &$value) {
                    $context[$context_variable_name] = $var;

                    $new[] = $partial->render($context);
                }

                $return = $new;
            } else {
                $context[$context_variable_name] = $variable;
                $return = $partial->render($context);
            }
        });

        return $return;
    }

    private function load_cached_partial($context) {
        $registers = $context->registers();
        $cached_partials = $registers['cached_partial'];

        $template_name = $context[$this->template_name];

        if ($cached = $cached_partials[$template_name]) {
            return $cached;
        }

        $source = $this->read_template_from_file_system($context);

        $partial = Template::parse($source);
        $cached_partials[$template_name] = $partial;
        $registers['cached_partials'] = $cached_partials;

        return $partial;
    }

    public function read_template_from_file_system($context) {
        $registers = $context->registers();
        $file_system = $registers['file_system'] || Template::file_system();

        $reflection = new \ReflectionMethod($file_system, 'read_template_file');
        switch($reflection->getNumberOfParameters()) {
        case 1:
            return $file_system->read_template_from_file($context[$this->template_name]);
        case 2:
            return $file_system->read_template_from_file($context[$this->template_name], $context);
        default:
            throw new \ArgumentException("file_system.read_template_file expects two parameters: (template_name, context)");
        }
    }
}

IncludeTag::init();

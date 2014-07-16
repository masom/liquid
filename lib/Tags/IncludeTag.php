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
        static::$Syntax = '/(' . Liquid::$PART_QuotedFragment . '+)(\s+(?:with|for)\s+(' . Liquid::$PART_QuotedFragment . '+))?/';
    }

    /**
     * @param string $tag_name
     * @param string $markup
     * @param array  $options
     * @throws \Liquid\Exceptions\SyntaxError
     */
    public function __construct($tag_name, $markup, $options) {
        parent::__construct($tag_name, $markup, $options);

        $matches = null;
        if (preg_match(static::$Syntax, $markup, $matches)) {
            $this->template_name = $matches[1];
            $this->variable_name = isset($matches[3]) ? $matches[3] : null;
            $this->attributes = array();

            preg_match_all(Liquid::$TagAttributes, $markup, $matches);
            foreach($matches[0] as $key => $value) {
                $this->attributes[$key] = $value;
            }
        } else {
            throw new \Liquid\Exceptions\SyntaxError("Error in tag 'include' - Valid syntax: include '[template]' (with|for) [object|collection]");
        }
    }

    public function _parse($tokens) {
    }

    /**
     * @return bool
     */
    public function is_blank() {
        return false;
    }

    /**
     * @param \Liquid\Context $context
     *
     * @return null
     */
    public function render(&$context) {
        $partial = $this->load_cached_partial($context);

        $variable = $context[$this->variable_name || substr($this->template_name, 1, strlen($this->template_name) -2 )];

        $attributes =& $this->attributes;

        $return = null;
        $context->stack(function($context) use (&$partial, &$variable, &$attributes, &$return) {
            foreach($attributes as $key => $value) {
                $context[$key] = $context[$value];
            }

            $tmp = explode('/', substr($this->template_name, 1, strlen($this->template_name) -2 ));
            $context_variable_name = end($tmp);

            if (is_array($variable)) {
                $new = array();
                foreach($variable as &$value) {
                    $context[$context_variable_name] = $value;

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

    /**
     * @param \Liquid\Context $context
     *
     * @return mixed
     */
    private function load_cached_partial($context) {
        $registers = $context->registers();
        $cached_partials = $registers['cached_partial'];

        $template_name = $context[$this->template_name];

        if (isset($cached_partials[$template_name]) && $cached = $cached_partials[$template_name]) {
            return $cached;
        }

        $source = $this->read_template_from_file_system($context);

        $partial = Template::parse($source);
        $cached_partials[$template_name] = $partial;
        $registers['cached_partials'] = $cached_partials;

        return $partial;
    }

    /**
     * @param \Liquid\$context
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function read_template_from_file_system($context) {
        $registers = $context->registers();
        $file_system = $registers['file_system'] ? $registers['file_system'] : Template::filesystem();

        $reflection = new \ReflectionMethod($file_system, 'read_template_file');
        switch($reflection->getNumberOfParameters()) {
        case 1:
            return $file_system->read_template_file($context[$this->template_name]);
        case 2:
            return $file_system->read_template_file($context[$this->template_name], $context);
        default:
            throw new \InvalidArgumentException("file_system.read_template_file expects two parameters: (template_name, context)");
        }
    }
}

IncludeTag::init();

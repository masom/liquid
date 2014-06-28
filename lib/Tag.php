<?php

namespace Liquid;

class Tag {

    protected $to;
    protected $from;

    protected $tag_name;
    protected $markup;
    protected $options;

    protected $nodelist;
    protected $warnings;

    public static function parse() {
        if (isset($this) && $this instanceof Tag) {
            // non static context
            return;
        }

        /** $tag_name, $markup, $tokens, array $options */
        $args = func_get_args();

        if (count($args) != 4)
        {
            throw new \InvalidArgumentException('4 parameters should have been passed.');
        }

        $tag = new static($args[0], $args[1], $args[3]);
        $tag->parse($args[2]);
        return $tag;
    }

    public function __construct($tag_name, $markup, array $options) {
        $this->tag_name = $tag_name;
        $this->markup = $markup;
        $this->options = $options;
    }

    public function options() {
        return $this->options;
    }

    public function nodelist() {
        return $this->nodelist;
    }
    
    public function warnings() {
        return $this->warnings;
    }

    public function name() {
        $lastNsPos = strrpos(__CLASS__, '\\');
        $namespace = substr(__CLASS__, 0, $lastNsPos);

        return strtolower(substr(__CLASS__, $lastNsPos + 1));
    }

    public function render($context) {
        return '';
    }

    public function isBlank() {
        return $this->blank ?: false;
    }

    public function parse_with_selected_parser($markup) {
        try {
            return $this->strict_parse($markup);
        } catch(\Liquid\Exceptions\SyntaxError $e) {
            $e->setMessage( $e->getMessage() . ' in "'. trim($markup) .'"');
            throw $e;
        }
    }
}

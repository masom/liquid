<?php


namespace Liquid\Tests\Lib;


use Liquid\Liquid;
use Liquid\Tag;


class CustomInclude extends Tag {
    protected static $Syntax;
    protected $template_name;

    public static function init() {
        static::$Syntax = '/(' . Liquid::$PART_QuotedFragment . '+)(\s+(?:with|for)\s+(' . Liquid::$PART_QuotedFragment . '+))?/';
    }

    public function __construct($tag_name, $markup, $tokens) {

        if (preg_match(static::$Syntax, $markup, $matches)) {
            $this->template_name = $matches[1];
        }
        parent::__construct($tag_name, $markup, $tokens);
    }

    public function _parse($tokens) {
    }

    public function is_blank() {
        return false;
    }

    public function render(&$context) {
        return substr($this->template_name, 1, strlen($this->template_name - 2));
    }
}

CustomInclude::init();

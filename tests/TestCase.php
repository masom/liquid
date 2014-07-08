<?php

namespace Liquid\Tests;

use \Liquid\Template;

class TestCase extends \PHPUnit_Framework_TestCase {

    protected function fixture($name) {
        return __DIR__ . '/fixtures/' . $name;
    }

    protected function assert_template_result($expected, $template, array $assigns = array(), $message = null ) {
        $this->assertEquals($expected, Template::parse($template)->render($assigns));
    }

    protected function assert_match_syntax_error($match, $template, array $assigns = array()) {
        try{
            Template::parse($template)->render($assigns);
            $this->fail('An SyntaxError should have been thrown.');
        } catch(\Liquid\Exceptions\SyntaxError $e){
            $this->assertEquals($match, $e->getMessage());
        }
    }
}

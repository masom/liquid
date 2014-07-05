<?php

namespace Liquid\Tests;

use \Liquid\Template;

class TestCase extends \PHPUnit_Framework_TestCase {

    protected function fixture($name) {
        return __DIR__ . '/fixtures/' . $name;
    }

    protected function assert_template_result($expected, $template, array $assigns = array(), $message = null ) {
        return $this->assertEquals($expected, Template::parse($template)->render($assigns));
    }
}

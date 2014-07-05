<?php

namespace Liquid\Tests;

use \Liquid\Template;

class TestCase extends \PHPUnit_Framework_TestCase {

    public function fixture($name) {
        return __DIR__ . '/fixtures/' . $name;
    }

    public function assert_template_result($expected, $template, array $assigns = array(), $message = null ) {
        return $this->assertEquals($expected, Template::parse($template)->render($assigns));
    }
}

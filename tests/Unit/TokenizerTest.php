<?php

namespace Liquid\Tests\Unit;


use \Liquid\Template;

class TokenizerTest extends \Liquid\Tests\TestCase {

    /** @var \ReflectionMethod */
    protected $method;

    protected function setUp() {
        if (!$this->method) {
            $this->method = new \ReflectionMethod('\Liquid\Template', 'tokenize');
            $this->method->setAccessible(true);
        }
    }

    protected function tokenize($source) {
        $template = new Template();

        return $this->method->invoke($template, $source);
    }

    public function test_tokenize_strings() {
        $this->assertEquals(array(' '), $this->tokenize(' '));
        $this->assertEquals(array('hello world'), $this->tokenize('hello world'));
    }

    public function test_tokenize_variables() {
        $this->assertEquals(array('{{funk}}'), $this->tokenize('{{funk}}'));

        $this->assertEquals(array(' ', '{{funk}}', ' '), $this->tokenize(' {{funk}} '));

        $expected = array(' ', '{{funk}}', ' ', '{{so}}', ' ', '{{brother}}', ' ');
        $this->assertEquals($expected, $this->tokenize(' {{funk}} {{so}} {{brother}} '));

        $this->assertEquals(array(' ', '{{ funk }}', ' '), $this->tokenize(' {{ funk }} '));
    }

    public function test_tokenize_blocks() {
        $this->assertEquals(array('{%comment%}'), $this->tokenize('{%comment%}'));
        $this->assertEquals(array(' ', '{%comment%}', ' '), $this->tokenize(' {%comment%} '));


        $expected = array(' ', '{%comment%}', ' ', '{%endcomment%}', ' ');
        $this->assertEquals($expected, $this->tokenize(' {%comment%} {%endcomment%} '));

        $expected = array('  ', '{% comment %}', ' ', '{% endcomment %}', ' ');
        $this->assertEquals($expected, $this->tokenize('  {% comment %} {% endcomment %} '));
    }
}

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
        $this->assertEquals(array(' '), $this->tokenize(' ')->tokens());
        $this->assertEquals(array('hello world'), $this->tokenize('hello world')->tokens());
    }

    public function test_tokenize_variables() {
        $this->assertEquals(array('{{funk}}'), $this->tokenize('{{funk}}')->tokens());

        $this->assertEquals(array(' ', '{{funk}}', ' '), $this->tokenize(' {{funk}} ')->tokens());

        $expected = array(' ', '{{funk}}', ' ', '{{so}}', ' ', '{{brother}}', ' ');
        $this->assertEquals($expected, $this->tokenize(' {{funk}} {{so}} {{brother}} ')->tokens());

        $this->assertEquals(array(' ', '{{ funk }}', ' '), $this->tokenize(' {{ funk }} ')->tokens());
    }

    public function test_tokenize_blocks() {
        $this->assertEquals(array('{%comment%}'), $this->tokenize('{%comment%}')->tokens());
        $this->assertEquals(array(' ', '{%comment%}', ' '), $this->tokenize(' {%comment%} ')->tokens());


        $expected = array(' ', '{%comment%}', ' ', '{%endcomment%}', ' ');
        $this->assertEquals($expected, $this->tokenize(' {%comment%} {%endcomment%} ')->tokens());

        $expected = array('  ', '{% comment %}', ' ', '{% endcomment %}', ' ');
        $this->assertEquals($expected, $this->tokenize('  {% comment %} {% endcomment %} ')->tokens());
    }
}

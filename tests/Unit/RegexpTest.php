<?php

namespace Liquid\Tests\Unit;

use \Liquid\Liquid;


class RegexpTest extends \Liquid\Tests\TestCase {

    public function test_empty() {
        $this->assertEquals(0, preg_match_all(Liquid::$QuotedFragment, '', $matches));
        $this->assertEquals(array(), $matches[0]);
    }

    public function test_quote() {
        $this->assertEquals(1, preg_match_all(Liquid::$QuotedFragment, '"arg 1"', $matches));
        $this->assertEquals(array('"arg 1"'), $matches[0]);
    }

    public function test_words() {
        $this->assertEquals(2, preg_match_all(Liquid::$QuotedFragment, 'arg1 arg2', $matches));
        $this->assertEquals(array('arg1', 'arg2'), $matches[0]);
    }

    public function test_tags() {
        $this->assertEquals(2, preg_match_all(Liquid::$QuotedFragment, '<tr> </tr>', $matches));
        $this->assertEquals(array('<tr>', '</tr>'), $matches[0]);

        $this->assertEquals(1, preg_match_all(Liquid::$QuotedFragment, '<tr></tr>', $matches));
        $this->assertEquals(array('<tr></tr>'), $matches[0]);

        $this->assertEquals(3, preg_match_all(Liquid::$QuotedFragment, '<style class="hello">\' </style>', $matches));
        $this->assertEquals(array('<style', 'class="hello">', '</style>'), $matches[0]);
    }

    public function test_double_quoted_words() {
        $this->assertEquals(3, preg_match_all(Liquid::$QuotedFragment, 'arg1 arg2 "arg 3"', $matches));
        $this->assertEquals(array('arg1', 'arg2', '"arg 3"'), $matches[0]);
    }

    public function test_single_quoted_words() {
        $this->assertEquals(3, preg_match_all(Liquid::$QuotedFragment, "arg1 arg2 'arg 3'", $matches));
        $this->assertEquals(array('arg1', 'arg2', "'arg 3'"), $matches[0]);
    }

    public function test_quoted_words_in_the_middle() {
        $this->assertEquals(4, preg_match_all(Liquid::$QuotedFragment, 'arg1 arg2 "arg 3" arg4   ', $matches));
        $this->assertEquals(array('arg1', 'arg2', '"arg 3"', 'arg4'), $matches[0]);
    }

    public function test_variable_parser() {
        $this->assertEquals(1, preg_match_all(Liquid::$VariableParser, 'var', $matches));
        $this->assertEquals(array('var'), $matches[0]);

        $this->assertEquals(2, preg_match_all(Liquid::$VariableParser, 'var.method', $matches));
        $this->assertEquals(array('var', 'method'), $matches[0]);

        $this->assertEquals(2, preg_match_all(Liquid::$VariableParser, 'var[method]', $matches));
        $this->assertEquals(array('var', '[method]'), $matches[0]);

        $this->assertEquals(3, preg_match_all(Liquid::$VariableParser, 'var[method][0]', $matches));
        $this->assertEquals(array('var', '[method]', '[0]'), $matches[0]);

        $this->assertEquals(4, preg_match_all(Liquid::$VariableParser, 'var[method][0].method', $matches));
        $this->assertEquals(array('var', '[method]', '[0]', 'method'), $matches[0]);
    }
}

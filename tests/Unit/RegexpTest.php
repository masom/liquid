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
}

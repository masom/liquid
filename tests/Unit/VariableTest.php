<?php

namespace Liquid\Tests\Unit;

use \Liquid\Variable;

class VariableTest extends \Liquid\Tests\TestCase {

    public function test_variable() {
        $var = new Variable('hello');
        $this->assertEquals('hello', $var->name());
    }

    public function test_filters() {
        $var = new Variable('hello | textileze');
        $this->assertEquals('hello', $var->name());

        $expected = array(
            array('textileze', array()),
        );
        $this->assertEquals($expected, $var->filters());

        $var = new Variable('hello | textileze | paragraph');
        $this->assertEquals('hello', $var->name());

        $expected = array(
            array('textileze', array()),
            array('paragraph', array())
        );
        $this->assertEquals($expected, $var->filters());

        $var = new Variable(' hello | strftime: \'%Y\'');
        $this->assertEquals('hello', $var->name());
        $this->assertEquals(array(array('strftime', array("'%Y'"))), $var->filters());

        $var = new Variable(" 'typo' | link_to: 'Typo', true ");
        $this->assertEquals("'typo'", $var->name());
        $expected = array(
            array('link_to', array("'Typo'", 'true'))
        );
        $this->assertEquals($expected, $var->filters());

        $var = new Variable(" 'typo' | link_to: 'Typo', false ");
        $this->assertEquals("'typo'", $var->name());
        $expected = array(
            array('link_to', array("'Typo'", 'false'))
        );
        $this->assertEquals($expected, $var->filters());

        $var = new Variable(" 'foo' | repeat: 3 ");
        $this->assertEquals("'foo'", $var->name());
        $this->assertEquals(array(array('repeat', array('3'))), $var->filters());

        $var = new Variable(" 'foo' | repeat: 3, 3 ");
        $this->assertEquals("'foo'", $var->name());
        $this->assertEquals(array(array('repeat', array('3','3'))), $var->fiters());

        $var = new Variable(" 'foo' | repeat: 3, 3, 3 ");
        $this->assertEquals("'foo'", $var->name());
        $this->assertEquals(array(array('repeat', array('3','3','3'))), $var->fiters());

        $var = new Variable("' hello | strftime: '%Y, okay?'");
        $this->assertEquals('hello', $var->name());
        $this->assertEquals(array(array('strftime', array("'%Y, okay?'"))), $var->filters());

        $var = new Variable(' hello | things: "%Y, okay?", \'the other one\'');
        $this->assertEquals('hello', $var->name());
        $this->assertEquals(array(array('things', array("\"%Y, okay?\"", "'the other one'"))), $var->filters());
    }

    public function test_filter_with_date_parameter() {

    }
}

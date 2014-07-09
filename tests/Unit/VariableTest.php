<?php

namespace Liquid\Tests\Unit;

use \Liquid\Liquid;
use \Liquid\Template;
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
        $this->assertEquals(array(array('repeat', array('3','3'))), $var->filters());


        $var = new Variable(" 'foo' | repeat: 3, 3, 3 ");
        $this->assertEquals("'foo'", $var->name());
        $this->assertEquals(array(array('repeat', array('3','3','3'))), $var->filters());


        $var = new Variable(" hello | strftime: '%Y, okay?'");
        $this->assertEquals('hello', $var->name());
        $this->assertEquals(array(array('strftime', array("'%Y, okay?'"))), $var->filters());

        $var = new Variable(' hello | things: "%Y, okay?", \'the other one\'');
        $this->assertEquals('hello', $var->name());
        $this->assertEquals(array(array('things', array("\"%Y, okay?\"", "'the other one'"))), $var->filters());
    }

    public function test_filter_with_date_parameter() {
        $var = new Variable(" '2006-06-06' | date: \"%m/%d/%Y\"");
        $this->assertEquals("'2006-06-06'", $var->name());
        $this->assertEquals(array(array('date', array('"%m/%d/%Y"'))), $var->filters());
    }

    public function test_filter_without_whitespace() {
        $var = new Variable('hello | textileze | paragraph');
        $this->assertEquals('hello', $var->name());

        $expected = array(
            array('textileze', array()),
            array('paragraph', array()),
        );
        $this->assertEquals($expected, $var->filters());

        $var = new Variable('hello|textileze|paragraph');
        $this->assertEquals('hello', $var->name());

        $expected = array(
            array('textileze', array()),
            array('paragraph', array())
        );
        $this->assertEquals($expected, $var->filters());

        $var = new Variable("hello|replace:'foo','bar'|textileze");
        $this->assertEquals('hello', $var->name());
        $expected = array(
            array('replace', array("'foo'", "'bar'")),
            array('textileze', array())
        );
        $this->assertEquals($expected, $var->filters());
    }

    public function test_symbol() {
        $var = new Variable("http://disney.com/logo.gif | image: 'med' ");

        $this->assertEquals('http://disney.com/logo.gif', $var->name());
        $this->assertEquals(array(array('image', array("'med'"))), $var->filters());
    }

    public function test_string_to_filter() {
        $var = new Variable("'http://disney.com/logo.gif' | image: 'med' ");
        $this->assertEquals("'http://disney.com/logo.gif'", $var->name());
        $this->assertEquals(array(array('image', array("'med'"))), $var->filters());
    }

    public function test_string_single_quoted() {
        $var = new Variable(" 'hello' ");
        $this->assertEquals("'hello'", $var->name());
    }

    public function test_string_double_quoted() {
        $var = new Variable(' "hello" ');
        $this->assertEquals('"hello"', $var->name());
    }

    public function test_integer() {
        $var = new Variable(' 1000 ');
        $this->assertEquals( '1000', $var->name());
    }

    public function test_float() {
        $var = new Variable(' 1000.01 ');
        $this->assertEquals( '1000.01', $var->name());
    }

    public function test_string_with_special_chars() {
        $var = new Variable(' \'hello! $!@.;"ddasd" \' ');
        $this->assertEquals('\'hello! $!@.;"ddasd" \'', $var->name());
    }

    public function test_string_dot() {
        $var = new Variable(' test.test ');
        $this->assertEquals('test.test', $var->name());
    }

    public function test_filter_with_keyword_arguments() {
        if (Template::error_mode() == Liquid::ERROR_MODE_STRICT) {
            $var = new Variable(' hello | things: greeting: "world", farewell: \'goodbye\'');
            $this->assertEquals('hello', $var->name());
            $expected = array(
                array('things', array("greeting: \"world\"", "farewell: 'goodbye'"))
            );
            $this->assertEquals($expected, $var->filters());
        } else {
            /**
            * FIXME LAX REGEX has trouble supporting single quotes.
            */
            $var = new Variable(' hello | things: greeting: "world", farewell: "goodbye"');
            $this->assertEquals('hello', $var->name());
            $expected = array(
                array('things', array("greeting: \"world\"", "farewell: \"goodbye\""))
            );
            $this->assertEquals($expected, $var->filters());
        }
    }

    public function test_lax_filter_argument_parsing() {
        $var = new Variable(" number_of_comments | pluralize: 'comment': 'comments' ", array('error_mode' => Liquid::ERROR_MODE_LAX));
        $this->assertEquals('number_of_comments', $var->name());

        $expected = array(
            array('pluralize', array("'comment'", "'comments'")),
        );
        $this->assertEquals($expected, $var->filters());
    }

    public function test_strict_filter_argument_parsing() {
        $old = Template::error_mode();
        Template::error_mode(Liquid::ERROR_MODE_STRICT);

        try{
            new Variable(' number_of_comments | pluralize: \'comment\': \'comments\' ');
            $this->fail("A SyntaxError should have been raised.");
        } catch(\Liquid\Exceptions\SyntaxError $e) {
        }

        Template::error_mode($old);
    }
}

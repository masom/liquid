<?php


namespace Liquid\Tests\Integration\Tags;


use Liquid\Tests\IntegrationTestCase;


class IncrementTagTest extends IntegrationTestCase {

    public function test_inc() {
        $this->assert_template_result('0','{%increment port %}', array());
        $this->assert_template_result('0 1','{%increment port %} {%increment port%}', array());
        $this->assert_template_result(
            '0 0 1 2 1',
            '{%increment port %} {%increment starboard%} ' .
            '{%increment port %} {%increment port%} ' .
            '{%increment starboard %}',
            array()
        );
    }

    public function test_dec() {
        $this->assert_template_result('9','{%decrement port %}', array('port' => 10));
        $this->assert_template_result('-1 -2','{%decrement port %} {%decrement port%}', array());
        $this->assert_template_result(
            '1 5 2 2 5',
            '{%increment port %} {%increment starboard%} ' .
            '{%increment port %} {%decrement port%} ' .
            '{%decrement starboard %}',
            array('port' => 1, 'starboard' => 5)
        );
    }
}

<?php

namespace Liquid\Tests\Integration;

class AssignTest extends \Liquid\Tests\TestCase {
    public function test_assigned_variable() {
        $this->assert_template_result(
            '.foo.',
            '{% assign foo = values %}.{{ foo[0] }}.',
            array('values' => array('foo', 'bar', 'baz'))
        );

        $this->assert_template_result(
            '.bar.',
            '{% assign foo = values %}.{{ foo[1] }}.',
            array('values' => array('foo', 'bar', 'baz'))
        );
    }

    public function test_assign_with_filter() {
        $this->assert_template_result(
            '.bar.',
            '{% assign foo = values | split: "," %}.{{ foo[1] }}.',
            array('values' => 'foo,bar,baz')
        ); 
    }

    public function test_assign_syntax_error() {
        $this->assert_match_syntax_error(
            "Syntax Error in 'assign' - Valid syntax: assign [var] = [source]",
            '{% assign foo not values %}.',
            array('values' => 'foo,bar,baz')
        );
    }
}

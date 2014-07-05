<?php

namespace Liquid\Tests\Integration;

class AssignTest extends \Liquid\Tests\TestCase {
    public function test_assigned_variable() {
        $this->assert_template_result( '.foo.', '{% assign foo = values %}.{{ foo[0] }}.', array('values' => array('foo', 'bar', 'baz')));
    }
}

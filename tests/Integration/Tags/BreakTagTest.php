<?php


namespace Liquid\Tests\Integration\Tags;


use Liquid\Tests\IntegrationTestCase;


class BreakTagTest extends IntegrationTestCase {

    public function test_break_with_no_block() {
        $assigns = array('i' => 1);
        $markup = '{% break %}';
        $expected = '';

        $this->assert_template_result($expected, $markup, $assigns);
    }
}

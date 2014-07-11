<?php


namespace Liquid\Tests\Integration\Tags;


use Liquid\Tests\IntegrationTestCase;


class ContinueTagTest extends IntegrationTestCase {

    public function test_continue_with_no_block() {
        $assigns = array();
        $markup = '{% continue %}';
        $expected = '';

        $this->assert_template_result($expected, $markup, $assigns);
    }

}

<?php

namespace Liquid\Tests\Integration;

//TOOD Implement the other tests.
class CaptureTest extends \Liquid\Tests\IntegrationTestCase {

    public function test_captures_block_content_in_variable() {
        $this->assert_template_result(
            "test string",
            "{% capture 'var' %}test string{% endcapture %}{{var}}",
            array()
        );
    }
}

<?php

namespace Liquid\Tests\Unit;

use \Liquid\Context;

class ContextTest extends \Liquid\Tests\TestCase {

    protected $context;

    protected function setUp() {
        $this->context = new Context();
    }

    protected function tearDown() {
        unset($this->context);
    }

    public function test_variables() {
        $this->context['string'] = 'string';
        $this->assertEquals('string', $this->context['string']);
    }
}

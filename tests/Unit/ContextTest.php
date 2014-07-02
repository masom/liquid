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

        $this->context['num'] = 5;
        $this->assertEquals(5, $this->context['num']);

        $this->context['time'] = new \DateTime('2006-06-06 12:00:00', new \DateTimeZone('UTC'));
        $expected = new \DateTime('2006-06-06 12:00:00', new \DateTimeZone('UTC'));
        $this->assertEquals($expected->getTimestamp(), $this->context['time']->getTimestamp());

        $this->context['date'] = gmdate('Y-m-d');
        $this->assertEquals(gmdate('Y-m-d'), $this->context['date']);

        $this->context['bool'] = true;
        $this->assertTrue($this->context['bool']);

        $this->context['bool'] = false;
        $this->assertFalse($this->context['bool']);

        $this->context['null'] = null;
        $this->assertNull($this->context['null']);
        $this->assertNull($this->context['null']);
    }
}

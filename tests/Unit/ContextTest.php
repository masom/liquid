<?php

namespace Liquid\Tests\Unit;

use \Liquid\Context;
use \Liquid\Strainer;

use \Liquid\Tests\Lib\ContextFilter;

class ContextTest extends \Liquid\Tests\TestCase {

    protected $context;

    protected function setUp() {
        Strainer::init();
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

    public function test_variables_not_existing() {
        $this->assertNull($this->context['does_not_exists']);
    }

    public function test_scoping() {
        $this->context->push();
        $this->context->pop();

        try {
            $this->context->pop();
            $this->fail('A ContextError should have been raised.');
        } catch(\Liquid\Exceptions\ContextError $e) {
        }

        try {
            $this->context->push();
            $this->context->pop();
            $this->context->pop();
            $this->fail('A ContextError should have been raised.');
        } catch(\Liquid\Exceptions\ContextError $e) {
        }
    }

    public function test_length_query() {
        $this->context['numbers'] = array(1,2,3,4);
        $this->assertEquals(4, $this->context['numbers.size']);
    }

    public function test_hyphenated_variable() {
        $this->context['oh-my'] = 'godz';
        $this->assertEquals('godz', $this->context['oh-my']);
    }

    public function test_add_filter() {
        $filter = new ContextFilter();

        $context = new Context();
        $context->add_filters($filter);
        $this->assertEquals('hi? hi!', $context->invoke('hi', 'hi?'));
    }

    public function test_only_intended_filters_make_it_here() {
        $filter = new ContextFilter();

        $context = new Context();
        $this->assertEquals('Wookie', $context->invoke('hi', 'Wookie'));

        $context->add_filters($filter);
        $this->assertEquals('Wookie hi!', $context->invoke('hi', 'Wookie'));
    }

    public function test_add_item_in_outer_scope() {

    }
}

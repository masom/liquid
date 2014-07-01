<?php

namespace Liquid\Tests\Unit;

use \Liquid\StandardFilters;
use \Liquid\Strainer;
use \Liquid\Tests\Lib\AccessScopeFilters;

class StrainerTest extends \Liquid\Tests\TestCase {

    protected function setUp() {
        Strainer::global_filter(new StandardFilters());
        Strainer::global_filter(new AccessScopeFilters());
    }

    public function test_strainer() {
        $strainer = Strainer::create(null);

        $this->assertEquals(5, $strainer->invoke('size', 'input'));
        $this->assertEquals('public', $strainer->invoke('public_filter'));
    }

    public function test_strainer_only_invokes_public_filter_methods() {
        $strainer = Strainer::create(null);

        $this->assertFalse($strainer->is_invokable('__test__'));
        $this->assertFalse($strainer->is_invokable('test'));
        $this->assertFalse($strainer->is_invokable('instance_eval'));
        $this->assertFalse($strainer->is_invokable('__construct'));
        $this->assertFalse($strainer->is_invokable('__send__'));
        $this->assertFalse($strainer->is_invokable('__call'));
        $this->assertFalse($strainer->is_invokable('__set'));
        $this->assertFalse($strainer->is_invokable('__get'));

        $this->assertTrue($strainer->is_invokable('size'));
    }
}

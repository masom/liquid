<?php

namespace Liquid\Tests\Unit;

use \Liquid\StandardFilters;
use \Liquid\Strainer;
use \Liquid\Tests\Lib\AccessScopeFilters;

class StrainerTest extends \Liquid\Tests\TestCase
{
    protected function setUp()
    {
        parent::setUp();

        Strainer::global_filter(new AccessScopeFilters());
    }

    public function test_strainer()
    {
        $strainer = Strainer::create();

        $this->assertEquals(5, $strainer->invoke('size', 'input'));
        $this->assertEquals('public', $strainer->invoke('public_filter'));
    }

    public function test_strainer_only_invokes_public_filter_methods()
    {
        $strainer = Strainer::create();

        $this->assertFalse($strainer->is_invokable('__test__'));
        $this->assertFalse($strainer->is_invokable('test'));
        $this->assertFalse($strainer->is_invokable('instance_eval'));
        $this->assertFalse($strainer->is_invokable('__construct'));
        $this->assertFalse($strainer->is_invokable('__send__'));
        $this->assertFalse($strainer->is_invokable('__set'));
        $this->assertFalse($strainer->is_invokable('__get'));

        $this->assertTrue($strainer->is_invokable('__call'));
        $this->assertTrue($strainer->is_invokable('size'));
    }

    public function test_strainer_returns_null_if_no_filter_method_found()
    {
        $strainer = Strainer::create();

        $this->assertNull($strainer->invoke('private_filter'));
        $this->assertNull($strainer->invoke('undef_the_filter'));
    }

    public function test_strainer_returns_first_arguments_if_no_method_and_arguments_given()
    {
        $strainer = Strainer::create();

        $this->assertEquals('password', $strainer->invoke('undef_the_method', 'password'));
    }

    public function test_strainer_only_allows_method_defined_in_filters()
    {
        $strainer = Strainer::create();
        $this->assertEquals('1 + 1', $strainer->invoke('instance_eval', '1 + 1'));
        $this->assertEquals('1 + 1', $strainer->invoke('eval', '1 + 1'));
        $this->assertEquals('puts', $strainer->invoke('__send__', 'puts', 'Hi Mom'));
        $this->assertEquals('puts', $strainer->invoke('eval', 'puts', 'Hi Mom'));
        $this->assertEquals('has_method?', $strainer->invoke('invoke', 'has_method?', 'invoke'));
    }
}

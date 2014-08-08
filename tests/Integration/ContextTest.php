<?php

namespace Liquid\Tests\Integration;

use Liquid\Context;
use Liquid\Liquid;
use \Liquid\Template;

use \Liquid\Tests\Lib\GlobalFilter;
use \Liquid\Tests\Lib\LocalFilter;

class ContextTest extends \Liquid\Tests\IntegrationTestCase
{

    protected static $previousErrorMode;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$previousErrorMode = Template::error_mode();
    }

    protected function setUp()
    {
        parent::setUp();


        Template::register_filter(new GlobalFilter());
    }

    protected function tearDown()
    {
        parent::tearDown();

        Template::error_mode(static::$previousErrorMode);
    }


    public function test_override_global_filter()
    {
        $this->assertEquals('Global test', Template::parse("{{ 'test' | notice }}")->render());
        $this->assertEquals('Local test', Template::parse("{{ 'test' | notice }}")->render(array(), array('filters' => array(new LocalFilter()))));
    }

    public function test_has_key_will_not_add_an_error_for_missing_keys()
    {
        Template::error_mode(Liquid::ERROR_MODE_STRICT);

        $context = new Context();
        $context->offsetExists('unknown');

        $this->assertEmpty($context->errors());
    }
}


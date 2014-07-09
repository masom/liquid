<?php

namespace Liquid\Tests\Integration;

use \Liquid\Template;

use \Liquid\Tests\Lib\GlobalFilter;
use \Liquid\Tests\Lib\LocalFilter;

class ContextTest extends \Liquid\Tests\IntegrationTestCase {

    public function setUp()
    {
        Template::register_filter(new GlobalFilter());
        parent::setUp();
    }


    public function test_override_global_filter() {
        $this->assertEquals('Global test', Template::parse("{{ 'test' | notice }}")->render());
        $this->assertEquals('Local test', Template::parse("{{ 'test' | notice }}")->render(array(), array('filters'=>array(new LocalFilter()))));
    }

}


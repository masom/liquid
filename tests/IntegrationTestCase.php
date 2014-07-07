<?php

namespace Liquid\Tests;

class IntegrationTestCase extends \Liquid\Tests\TestCase {
}

\Liquid\Template::register_tag('foobar', '\Liquid\Tests\Lib\FoobarTag');

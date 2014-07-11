<?php


namespace Liquid\Tests\Integration;


use Liquid\Template;
use Liquid\Tests\Lib\CanadianMoneyFilter;
use Liquid\Tests\Lib\MoneyFilter;


class HashOrderingTest extends \Liquid\Tests\IntegrationTestCase {

    public function test_global_register_order() {
        Template::register_filter(new MoneyFilter());
        Template::register_filter(new CanadianMoneyFilter());

        $this->assertEquals(' 1000$ CAD ', Template::parse("{{1000 | money}}")->render(null, null));
    }
}

<?php


namespace Liquid\Tests\Integration;


use Liquid\Template;
use Liquid\Tests\Lib\CanadianMoneyFilter;
use Liquid\Tests\Lib\MoneyFilter;


class FiltersInTemplateTest extends \Liquid\Tests\IntegrationTestCase {
    public function test_local_global() {
        Template::register_filter(new MoneyFilter());

        $this->assertEquals( " 1000$ ", Template::parse("{{1000 | money}}")->render(null, null));
        $this->assertEquals( " 1000$ CAD ", Template::parse("{{1000 | money}}")->render(null, array('filters' => new CanadianMoneyFilter())));
        $this->assertEquals( " 1000$ CAD ", Template::parse("{{1000 | money}}")->render(null, array('filters' => array(new CanadianMoneyFilter()))));
    }

    public function test_local_filter_with_deprecated_syntax() {
        $this->assertEquals( " 1000$ CAD ", Template::parse("{{1000 | money}}")->render(null, new CanadianMoneyFilter()));
        $this->assertEquals( " 1000$ CAD ", Template::parse("{{1000 | money}}")->render(null, array(new CanadianMoneyFilter())));
    }
}

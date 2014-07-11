<?php


namespace Liquid\Tests\Integration;


use Liquid\Template;
use Liquid\Tests\IntegrationTestCase;
use Liquid\Tests\Lib\SecurityFilter;


class SecurityTest extends IntegrationTestCase {
    public function test_no_instance_eval() {
        $test = ' {{ \'1+1\' | instance_eval }} ';
        $expected = ' 1+1 ';

        $this->assertEquals($expected, Template::parse($test)->render(null));
    }

    public function test_no_existing_instance_eval() {
        $test = ' {{ \'1+1\' | __instance_eval__ }} ';
        $expected = ' 1+1 ';

        $this->assertEquals($expected, Template::parse($test)->render(null));
    }


    public function test_no_instance_eval_after_mixing_in_new_filter() {
        $test = ' {{ \'1+1\' | instance_eval }} ';
        $expected = ' 1+1 ';

        $this->assertEquals($expected, Template::parse($test)->render(null));
    }


    public function test_no_instance_eval_later_in_chain() {
        $test = ' {{ \'1+1\' | add_one | instance_eval }} ';
        $expected = ' 1+1 + 1 ';

        $this->assertEquals($expected, Template::parse($test)->render(null, array('filters' => new SecurityFilter())));
    }
}

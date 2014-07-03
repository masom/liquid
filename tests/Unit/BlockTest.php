<?php

namespace Liquid\Tests\Unit;

use \Liquid\Template;


class BlockTest extends \Liquid\Tests\TestCase {
    public function test_blankspace() {
        $template = Template::parse("  ");
        $this->assertEquals(array("  "), $template->root()->nodelist());
    }

    public function test_variable_beginning() {
        $template = Template::parse("{{funk}}  ");

        $nodelist = $template->root()->nodelist();
        $this->assertEquals(2, count($nodelist));;
        $this->assertInstanceOf('\Liquid\Variable', $nodelist[0]);
        $this->assertInternalType('string', $nodelist[1]);
    }

    public function test_variable_end() {
        $template = Template::parse("  {{funk}}");

        $nodelist = $template->root()->nodelist();
        $this->assertEquals(2, count($nodelist));
        $this->assertInternalType('string', $nodelist[0]);
        $this->assertInstanceOf('\Liquid\Variable', $nodelist[1]);
    }

    public function test_variable_middle() {
        $template = Template::parse("  {{funk}}  ");

        $nodelist = $template->root()->nodelist();
        $this->assertEquals(3, count($nodelist));
        $this->assertInternalType('string', $nodelist[2]);
        $this->assertInstanceOf('\Liquid\Variable', $nodelist[1]);
        $this->assertInternalType('string', $nodelist[2]);

    }
}

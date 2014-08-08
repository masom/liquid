<?php

namespace Liquid\Tests\Unit;

use \Liquid\Block;
use \Liquid\Template;

class BlockTest extends \Liquid\Tests\TestCase
{

    public function test_blankspace()
    {
        /** @var Template $template */
        $template = Template::parse("  ");
        $this->assertEquals(array("  "), $template->root()->nodelist()->nodes());
    }

    public function test_variable_beginning()
    {
        /** @var Template $template */
        $template = Template::parse("{{funk}}  ");

        $nodelist = $template->root()->nodelist();
        $this->assertEquals(2, count($nodelist));;
        $this->assertInstanceOf('\Liquid\Variable', $nodelist[0]);
        $this->assertInternalType('string', $nodelist[1]);
    }

    public function test_variable_end()
    {
        $template = Template::parse("  {{funk}}");

        $nodelist = $template->root()->nodelist();
        $this->assertEquals(2, count($nodelist));
        $this->assertInternalType('string', $nodelist[0]);
        $this->assertInstanceOf('\Liquid\Variable', $nodelist[1]);
    }

    public function test_variable_middle()
    {
        $template = Template::parse("  {{funk}}  ");

        $nodelist = $template->root()->nodelist();
        $this->assertEquals(3, count($nodelist));
        $this->assertInternalType('string', $nodelist[2]);
        $this->assertInstanceOf('\Liquid\Variable', $nodelist[1]);
        $this->assertInternalType('string', $nodelist[2]);
    }

    public function test_variable_many_embedded_fragments()
    {
        $template = Template::parse("  {{funk}} {{so}} {{brother}} ");
        $nodelist = $template->root()->nodelist();
        $this->assertEquals(7, count($nodelist));

        $expected = array('string', 'Variable', 'string', 'Variable', 'string', 'Variable', 'string');
        $this->assertEquals($expected, $this->block_types($nodelist));
    }

    public function test_with_block()
    {
        /** @var Template $template */
        $template = Template::parse("  {% comment %} {% endcomment %} ");
        $nodelist = $template->root()->nodelist();
        $this->assertEquals(array('string', 'Comment', 'string'), $this->block_types($nodelist));
        $this->assertEquals(3, count($nodelist));
    }

    public function test_with_custom_tag()
    {
        $this->markTestSkipped('Problem with Block->parse (shift on non-object)');
        Template::register_tag("testtag", '\Liquid\Block');
        try {
            $template = Template::parse("{% testtag %} {% endtesttag %}");
        } catch (\Liquid\Exceptions\LiquidException $e) {
            $this->fail('An exception should NOT have been thrown: ' . $e->getMessage());
        }
    }

    protected function block_types($nodes)
    {
        $tokens = array();
        foreach ($nodes as $token) {
            if (is_object($token)) {
                $type = $this->getBaseClassName(get_class($token));
            } else {
                $type = gettype($token);
            }
            $tokens[] = $type;
        }
        return $tokens;
    }

    protected function getBaseClassName($class)
    {
        $path = explode('\\', $class);
        return array_pop($path);
    }
}

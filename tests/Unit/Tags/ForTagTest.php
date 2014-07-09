<?php

namespace Liquid\Tests\Unit\Tags;

use \Liquid\Template;

class ForTagTest extends \Liquid\Tests\TestCase {

    public function test_for_nodelist() {
        $template = Template::parse('{% for item in items %}FOR{% endfor %}');

        $nodelist = $template->root()->nodelist();
        $this->assertEquals(array('FOR'), $nodelist[0]->nodelist()->nodes());
    }

    public function test_for_else_nodelist() {
        $template = Template::parse('{% for item in items %}FOR{% else %}ELSE{% endfor %}');
        $nodelist = $template->root()->nodelist();
        $this->assertEquals(array('FOR', 'ELSE'), $nodelist[0]->nodelist()->nodes());
    }
}

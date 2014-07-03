<?php

namespace Liquid\Tests\Unit\Tags;

use \Liquid\Template;

class IfTagTest extends \Liquid\Tests\TestCase {
    public function test_if_nodelist() {
        $template = Template::parse('{% if true %}IF{% else %}ELSE{% endif %}');

        $nodelist = $template->root()->nodelist();

        $this->assertEquals(array('IF', 'ELSE'), $nodelist[0]->nodelist());
    }
}

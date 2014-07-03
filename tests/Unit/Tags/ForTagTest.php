<?php

namespace Liquid\Tests\Unit\Tags;

use \Liquid\Template;

class ForTagTest extends \Liquid\Tests\TestCase {

    public function test_for_nodelist() {
        $template = Template::parse('{% for item in items %}FOR{% endfor %}');

        $nodelist = $template->root()->nodelist();
        $this->assertEquals(array('FOR'), $nodelist[0]->nodelist());
    }
}

<?php

namespace Liquid\Tests\Unit\Tags;

use \Liquid\Template;

class CaseTagTest extends \Liquid\Tests\TestCase {
    public function test_case_nodelist() {
        $template = Template::parse('{% case var %}{% when true %}WHEN{% else %}ELSE{% endcase %}');
        $nodelist = $template->root()->nodelist();

        $this->assertEquals(array('WHEN','ELSE'), $nodelist[0]->nodelist());
    }
}

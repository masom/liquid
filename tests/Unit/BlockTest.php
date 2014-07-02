<?php

namespace Liquid\Tests\Unit;

use \Liquid\Template;


class BlockTest extends \Liquid\Tests\TestCase {
    public function test_blankspace() {
        $template = Template::parse("  ");
        $this->assertEquals(array("  "), $template->root()->nodelist());
    }
}

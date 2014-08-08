<?php

namespace Liquid\Tests\Unit;

use \Liquid\Template;

class TemplateTest extends \Liquid\Tests\TestCase
{

    public function test_sets_default_localization_in_context_with_quick_initialization()
    {
        $t = new Template();
        $t->parse('{{foo}}');
    }
}

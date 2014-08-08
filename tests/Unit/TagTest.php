<?php

namespace Liquid\Tests\Unit;

use \Liquid\Context;
use \Liquid\Tag;

class TagTest extends \Liquid\Tests\TestCase
{

    public function test_tag()
    {
        $tag = Tag::parse('tag', array(), array(), array());
        $this->assertEquals('liquid::tag', $tag->name());
        $this->assertEquals('', $tag->render(new Context()));
    }
}

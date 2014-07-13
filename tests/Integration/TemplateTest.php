<?php

namespace Liquid\Tests\Integration;

use \Liquid\Context;
use \Liquid\Template;
use Liquid\Tests\Lib\ErroneousDrop;
use Liquid\Tests\Lib\SomethingWithLength;
use Liquid\Tests\Lib\TemplateContextDrop;


class TemplateTest extends \Liquid\Tests\IntegrationTestCase {

    public function test_instance_assigns_persist_on_same_template_object_between_parses() {
        $t = new Template();
        $this->assertEquals( 'from instance assigns', $t->parse("{% assign foo = 'from instance assigns' %}{{ foo }}")->render());
        $this->assertEquals( 'from instance assigns', $t->parse("{{ foo }}")->render());
    }

    public function test_instance_assigns_persist_on_same_template_parsing_between_renders() {
        $t = Template::parse("{{ foo }}{% assign foo = 'foo' %}{{ foo }}");
        $this->assertEquals('foo', $t->render());
        $this->assertEquals('foofoo', $t->render());
    }

    public function test_custom_assigns_do_not_persist_on_same_template() {
        $t = new Template();
        $this->assertEquals('from custom assigns', $t->parse("{{ foo }}")->render(array('foo' => 'from custom assigns')));
        $this->assertEquals('', $t->parse("{{ foo }}")->render());
    }

    public function test_custom_assigns_squash_instance_assigns() {
        $t = new Template();
        $this->assertEquals('from instance assigns', $t->parse("{% assign foo = 'from instance assigns' %}{{ foo }}")->render());
        $this->assertEquals('from custom assigns', $t->parse("{{ foo }}")->render(array('foo' => 'from custom assigns')));
    }

    public function test_persistent_assigns_squash_instance_assigns() {
        $t = new Template();
        $this->assertEquals('from instance assigns', $t->parse("{% assign foo = 'from instance assigns' %}{{ foo }}")->render());
        $assigns = $t->assigns();
        $assigns['foo'] = 'from persistent assigns';
        $this->assertEquals('from persistent assigns', $t->parse("{{ foo }}")->render());
    }

    public function test_lambda_is_called_once_from_persistent_assigns_over_multiple_parses_and_renders() {
        $global = 0;
        $t = new Template();
        $assigns = $t->assigns();
        $assigns['number'] = function() use (&$global){ $global += 1; return $global; };

        $this->assertEquals('1', $t->parse("{{number}}")->render());
        $this->assertEquals('1', $t->parse("{{number}}")->render());
        $this->assertEquals('1', $t->render());
    }

    public function test_lambda_is_called_once_from_custom_assigns_over_multiple_parses_and_renders() {
        $global = 0;
        $t = new Template();
        $assigns = new \ArrayObject(array('number' => function() use (&$global){ $global += 1; return $global; }));
        $this->assertEquals('1', $t->parse("{{number}}")->render($assigns));
        $this->assertEquals('1', $t->parse("{{number}}")->render($assigns));
        $this->assertEquals('1', $t->render($assigns));
    }

    public function test_resource_limits_works_with_custom_length_method() {
        /** @var Template $t */
        $t = Template::parse("{% assign foo = bar %}");
        $limits = $t->resource_limits();
        $limits["render_length_limit"] = 42;

        $this->assertEquals("", $t->render(array("bar" => new SomethingWithLength())));
    }

    public function test_resource_limits_render_length() {
        /** @var Template $t */
        $t = Template::parse("0123456789");
        $limits = $t->resource_limits();
        $limits["render_length_limit"] = 5;

        $this->assertEquals("Liquid error: Memory limits exceeded", $t->render());

        $this->assertTrue($limits['reached']);
        $limits["render_length_limit"] = 10;
        $this->assertEquals( "0123456789", $t->render());
        $this->assertNotNull($limits['render_length_current']);
    }

    public function test_resource_limits_render_score() {
        $this->markTestSkipped('Problem with shift on a non-object ( Block )');
        /** @var Template $t */
        $t = Template::parse("{% for a in (1..10) %} {% for a in (1..10) %} foo {% endfor %} {% endfor %}");
        $limits =& $t->resource_limits();
        $limits["render_score_limit"] = 50;
        $this->assertEquals( "Liquid error: Memory limits exceeded", $t->render());
        $this->assertTrue($limits['reached']);

        $t = Template::parse("{% for a in (1..100) %} foo {% endfor %}");
        $limits =& $t->resource_limits();
        $limits["render_score_limit"] = 50;
        $this->assertEquals("Liquid error: Memory limits exceeded", $t->render());
        $this->assertTrue($limits['reached']);
        $limits["render_score_limit"] = 200;
        $this->assertEquals( (" foo " * 100), $t->render());
        $this->assertNotNull($limits['render_score_current']);
    }

    public function test_resource_limits_assign_score() {
        /** @var Template $t */
        $t = Template::parse("{% assign foo = 42 %}{% assign bar = 23 %}");
        $limits = $t->resource_limits();
        $limits['assign_score_limit'] = 1;

        $this->assertEquals("Liquid error: Memory limits exceeded", $t->render());

        $this->assertTrue($limits['reached']);
        $limits['assign_score_limit'] = 2;
        $this->assertEquals( "", $t->render());
        $this->assertNotNull($limits['assign_score_current']);
    }

    public function test_resource_limits_aborts_rendering_after_first_error() {
        /** @var Template $t */
        $t = Template::parse("{% for a in (1..100) %} foo1 {% endfor %} bar {% for a in (1..100) %} foo2 {% endfor %}");
        $limits = $t->resource_limits();
        $limits["render_score_limit"] = 50;

        $this->assertEquals("Liquid error: Memory limits exceeded", $t->render());
        $this->assertTrue($limits['reached']);
    }

    public function test_resource_limits_hash_in_template_gets_updated_even_if_no_limits_are_set() {
        /** @var Template $t */
        $t = Template::parse("{% for a in (1..100) %} {% assign foo = 1 %} {% endfor %}");
        $t->render();
        $limits = $t->resource_limits();
        $this->assertTrue($limits['assign_score_current'] > 0);
        $this->assertTrue($limits['render_score_current'] > 0);
        $this->assertTrue($limits['render_length_current'] > 0);
    }

    public function test_can_use_drop_as_context() {
        $t = new Template();
        $registers = $t->registers();
        $registers['lulz'] = 'haha';

        $drop = new TemplateContextDrop();
        $this->assertEquals('fizzbuzz', $t->parse('{{foo}}')->render($drop));
        $this->assertEquals('bar', $t->parse('{{bar}}')->render($drop));
        $this->assertEquals('haha', $t->parse("{{baz}}")->render($drop));
    }

    public function test_render_bang_force_rethrow_errors_on_passed_context() {

        $context = new Context(array('drop' => new ErroneousDrop()));
        $t = new Template();
        $t->parse('{{ drop.bad_method }}');

        try{
            $t->render($context);
        }catch(\Exception $e){
            $this->assertEquals( 'ruby error in drop', $e->getMessage());
        }
    }
}

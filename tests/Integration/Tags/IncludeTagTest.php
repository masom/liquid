<?php


namespace Liquid\Tests\Integration\Tags;


use Liquid\Exceptions\StackLevelError;
use Liquid\Liquid;
use Liquid\Template;
use Liquid\Tests\IntegrationTestCase;
use Liquid\Tests\Lib\CountingFileSystem;
use Liquid\Tests\Lib\InfiniteFileSystem;
use Liquid\Tests\Lib\OtherFileSystem;
use Liquid\Tests\Lib\TestFileSystem;


class IncludeTagTest extends IntegrationTestCase {
    public function setup() {
        Template::filesystem(new TestFileSystem());
    }

    public function test_include_tag_looks_for_file_system_in_registers_first() {
        $this->assertEquals(
            'from OtherFileSystem',
            Template::parse("{% include 'pick_a_source' %}")->render(array(), array('registers' => array('file_system' => new OtherFileSystem() )))
        );
    }


    public function test_include_tag_with() {
        $this->assert_template_result(
            "Product: Draft 151cm ",
            "{% include 'product' with products[0] %}",
            array("products" => array(array('title' => 'Draft 151cm'), array('title' => 'Element 155cm')))
        );
    }

    public function test_include_tag_with_default_name() {
        $this->assert_template_result(
            "Product: Draft 151cm ",
            "{% include 'product' %}",
            array("product" => array('title' => 'Draft 151cm'))
        );
    }

    public function test_include_tag_for() {
        $this->assert_template_result(
            "Product: Draft 151cm Product: Element 155cm ",
            "{% include 'product' for products %}",
            array("products" => array(array('title' => 'Draft 151cm'), array('title' => 'Element 155cm')))
        );
    }

    public function test_include_tag_with_local_variables() {
        $this->assert_template_result("Locale: test123 ", "{% include 'locale_variables' echo1: 'test123' %}");
    }

    public function test_include_tag_with_multiple_local_variables() {
        $this->assert_template_result(
            "Locale: test123 test321",
            "{% include 'locale_variables' echo1: 'test123', echo2: 'test321' %}"
        );
    }

    public function test_include_tag_with_multiple_local_variables_from_context() {
        $this->assert_template_result(
            "Locale: test123 test321",
            "{% include 'locale_variables' echo1: echo1, echo2: more_echos.echo2 %}",
            array('echo1' => 'test123', 'more_echos' => array("echo2" => 'test321'))
        );
    }

    public function test_nested_include_tag() {
        $this->assert_template_result("body body_detail", "{% include 'body' %}");

        $this->assert_template_result("header body body_detail footer", "{% include 'nested_template' %}");
    }

    public function test_nested_include_with_variable() {
        $this->assert_template_result(
            "Product: Draft 151cm details ",
            "{% include 'nested_product_template' with product %}",
            array("product" => array("title" => 'Draft 151cm'))
        );

        $this->assert_template_result(
            "Product: Draft 151cm details Product: Element 155cm details ",
            "{% include 'nested_product_template' for products %}",
            array("products" => array(array("title" => 'Draft 151cm'), array("title" => 'Element 155cm')))
        );
    }

    public function test_recursively_included_template_does_not_produce_endless_loop() {
        Template::filesystem(new InfiniteFileSystem());

        try {
            /** @var Template $template */
            $template = Template::parse("{% include 'loop' %}");
            $template->rethrow_errors(true);
            $template->render();
            $this->fail('A StackLevelError should have been raised.');
        } catch (StackLevelError $e ){

        }
    }

    public function test_backwards_compatability_support_for_overridden_read_template_file() {
        $fs = new InfiniteFileSystem();
        $fs->set_response("- hi mom");

        Template::parse("{% include 'hi_mom' %}")->render();
    }

    public function test_dynamically_choosen_template() {
        $this->assert_template_result("Test123", "{% include template %}", array("template" => 'Test123'));
        $this->assert_template_result("Test321", "{% include template %}", array("template" => 'Test321'));

        $this->assert_template_result(
            "Product: Draft 151cm ", "{% include template for product %}",
            array("template" => 'product', 'product' => array('title' => 'Draft 151cm'))
        );
    }

    public function test_include_tag_caches_second_read_of_same_partial() {
        $fs = new CountingFileSystem();
        /** @var Template $template */
        $template = Template::parse("{% include 'pick_a_source' %}{% include 'pick_a_source' %}");
        $this->assertEquals(
            'from CountingFileSystemfrom CountingFileSystem',
            $template->render(array(), array('registers' => array('file_system' => $fs)))
        );
        $this->assertEquals(1, $fs->count());
    }

    public function test_include_tag_doesnt_cache_partials_across_renders() {
        $fs = new CountingFileSystem();
        $this->assertEquals(
            'from CountingFileSystem',
            Template::parse("{% include 'pick_a_source' %}")->render(array(), array('registers' => array('file_system' => $fs)))
        );

        $this->assertEquals(1, $fs->count());

        $this->assertEquals(
            'from CountingFileSystem',
            Template::parse("{% include 'pick_a_source' %}")->render(array(), array('registers' => array('file_system' => $fs)))
        );
        $this->assertEquals(2, $fs->count());
    }

    public function test_include_tag_within_if_statement() {
        $this->assert_template_result("foo_if_true", "{% if true %}{% include 'foo_if_true' %}{% endif %}");
    }

    public function test_custom_include_tag() {
        $tags = Template::tags();
        $original_tag = $tags['include'];

        Template::register_tag('include', '\Liquid\Tests\Lib\CustomInclude');

        try{
            $this->assertEquals(
                "custom_foo",
                Template::parse("{% include 'custom_foo' %}")->render()
            );
        } catch(\Exception $e) {
        }

        Template::register_tag('include', $original_tag);
    }

    public function test_custom_include_tag_within_if_statement() {
        $tags = Template::tags();
        $original_tag = $tags['include'];
        Template::register_tag('include', '\Liquid\Tests\Lib\CustomInclude');

        try {
            $this->assertEquals(
                "custom_foo_if_true",
                Template::parse("{% if true %}{% include 'custom_foo_if_true' %}{% endif %}")->render()
            );
        } catch (\Exception $e) {
        }

        Template::register_tag('include', $original_tag);
    }

    public function test_does_not_add_error_in_strict_mode_for_missing_variable() {
        Template::filesystem(new TestFileSystem());

        /** @var Template $a */
        $a = Template::parse(' {% include "nested_template" %}');
        $a->rethrow_errors(true);
        $a->render();
        $this->assertEmpty($a->errors());
    }
}

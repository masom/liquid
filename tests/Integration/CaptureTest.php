<?php

namespace Liquid\Tests\Integration;

use Liquid\Template;


class CaptureTest extends \Liquid\Tests\IntegrationTestCase {

    public function test_captures_block_content_in_variable() {
        $this->assert_template_result(
            "test string",
            "{% capture 'var' %}test string{% endcapture %}{{var}}",
            array()
        );
    }

    public function test_capture_to_variable_from_outer_scope_if_existing() {
        $template_source = <<<END_TEMPLATE
        {% assign var = '' %}
            {% if true %}
            {% capture var %}first-block-string{% endcapture %}
            {% endif %}
            {% if true %}
            {% capture var %}test-string{% endcapture %}
            {% endif %}
            {{var}}
END_TEMPLATE;
        /** @var Template $template */
        $template = Template::parse($template_source);
        $rendered = $template->render();

        $this->assertEquals( "test-string", preg_replace('/\s/', '', $rendered));
    }

    public function test_assigning_from_capture() {
        $template_source = <<<END_TEMPLATE
        {% assign first = '' %}
        {% assign second = '' %}
        {% for number in (1..3) %}
        {% capture first %}{{number}}{% endcapture %}
        {% assign second = first %}
        {% endfor %}
        {{ first }}-{{ second }}
END_TEMPLATE;
        /** @var Template $template */
        $template = Template::parse($template_source);
        $rendered = $template->render();
        $this->assertEquals( "3-3", preg_replace('/\s/', '', $rendered));
    }
}

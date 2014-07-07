<?php

namespace Liquid\Tests\Integration;

class BlankTest extends \Liquid\Tests\IntegrationTestCase {

    const N = 10;

    protected function wrap_in_for($body) {
        return "{% for i in (1..". static::N .") %}{$body}{% endfor %}";
    }

    protected function wrap_in_if($body) {
        return "{% if true %}{$body}{% endif %}";
    }

    protected function wrap($body) {
        return $this->wrap_in_for($body) . $this->wrap_in_if($body);
    }

    protected function repeat($content, $count = null) {
        if ($count == null) {
            $count = static::N;
        }
        return implode("", array_fill(0, $count, $content));
    }

    public function test_new_tags_are_not_blank_by_default() {
        $this->assert_template_result($this->repeat(" "), $this->wrap_in_for("{% foobar %}"));
    }

    public function test_loops_are_blank() {
        $this->assert_template_result("", $this->wrap_in_for(" "));
    }

    public function test_if_else_are_blank() {
        $this->assert_template_result("", "{% if true %} {% elsif false %} {% else %} {% endif %}");
    }
    public function test_unless_is_blank() {
        $this->assert_template_result("", $this->wrap("{% unless true %} {% endunless %}"));
    }

    public function test_mark_as_blank_only_during_parsing() {
        $this->assert_template_result($this->repeat(" ", static::N + 1), $this->wrap(" {% if false %} this never happens, but still, this block is not blank {% endif %}"));
    }

    public function test_comments_are_blank() {
        $this->assert_template_result("", $this->wrap(" {% comment %} whatever {% endcomment %} "));
    }

    public function test_captures_are_blank() {
        $this->assert_template_result("", $this->wrap(" {% capture foo %} whatever {% endcapture %} "));
    }
}

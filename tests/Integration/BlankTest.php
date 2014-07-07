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

    public function test_new_tags_are_not_blank_by_default() {
        $this->assert_template_result(implode("", array_fill(0, static::N, " ")), $this->wrap_in_for("{% foobar %}"));
    }
}

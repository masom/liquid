<?php

namespace Liquid\Tests\Integration\Tags;

class RawTagTest extends \Liquid\Tests\IntegrationTestCase {
    public function test_tag_in_raw() {
        $this->assert_template_result(
            '{% comment %} test {% endcomment %}',
            '{% raw %}{% comment %} test {% endcomment %}{% endraw %}'
        );
    }

    public function test_output_in_raw() {
        $this->assert_template_result( '{{ test }}', '{% raw %}{{ test }}{% endraw %}');
    }

    public function test_open_tag_in_raw() {
        $this->assert_template_result(
            ' Foobar {% invalid ',
            '{% raw %} Foobar {% invalid {% endraw %}'
        );

        $this->assert_template_result(
            ' Foobar invalid %} ',
            '{% raw %} Foobar invalid %} {% endraw %}'
        );
        $this->assert_template_result( ' Foobar {{ invalid ', '{% raw %} Foobar {{ invalid {% endraw %}');
        $this->assert_template_result( ' Foobar invalid }} ', '{% raw %} Foobar invalid }} {% endraw %}');
        $this->assert_template_result( ' Foobar {% invalid {% {% endraw ', '{% raw %} Foobar {% invalid {% {% endraw {% endraw %}');
        $this->assert_template_result( ' Foobar {% {% {% ', '{% raw %} Foobar {% {% {% {% endraw %}');
        $this->assert_template_result( ' test {% raw %} {% endraw %}', '{% raw %} test {% raw %} {% {% endraw %}endraw %}');
        $this->assert_template_result( ' Foobar {{ invalid 1', '{% raw %} Foobar {{ invalid {% endraw %}{{ 1 }}');
    }
}

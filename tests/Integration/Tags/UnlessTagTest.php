<?php


namespace Liquid\Tests\Integration\Tags;


use Liquid\Tests\IntegrationTestCase;


class UnlessTagTest extends IntegrationTestCase {

    public function test_unless() {
        $this->assert_template_result('  ',' {% unless true %} this text should not go into the output {% endunless %} ');
        $this->assert_template_result(
            '  this text should go into the output  ',
            ' {% unless false %} this text should go into the output {% endunless %} '
        );
        $this->assert_template_result('  you rock ?','{% unless true %} you suck {% endunless %} {% unless false %} you rock {% endunless %}?');
    }

    public function test_unless_else() {
        $this->assert_template_result(' YES ','{% unless true %} NO {% else %} YES {% endunless %}');
        $this->assert_template_result(' YES ','{% unless false %} YES {% else %} NO {% endunless %}');
        $this->assert_template_result(' YES ','{% unless "foo" %} NO {% else %} YES {% endunless %}');
    }

    public function test_unless_in_loop() {
        $this->assert_template_result('23', '{% for i in choices %}{% unless i %}{{ forloop.index }}{% endunless %}{% endfor %}', array('choices' => array(1, null, false)));
    }

    public function test_unless_else_in_loop() {
        $this->assert_template_result(' TRUE  2  3 ', '{% for i in choices %}{% unless i %} {{ forloop.index }} {% else %} TRUE {% endunless %}{% endfor %}', array('choices' => array(1, null, false)));
    }
}

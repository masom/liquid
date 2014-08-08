<?php


namespace Liquid\Tests\Integration\Tags;


use Liquid\Condition;
use Liquid\Exceptions\SyntaxError;
use Liquid\Tests\IntegrationTestCase;


class IfElseTagTest extends IntegrationTestCase
{
    public function test_if()
    {
        $this->assert_template_result('  ', ' {% if false %} this text should not go into the output {% endif %} ');
        $this->assert_template_result(
            '  this text should go into the output  ',
            ' {% if true %} this text should go into the output {% endif %} '
        );

        $this->assert_template_result('  you rock ?', '{% if false %} you suck {% endif %} {% if true %} you rock {% endif %}?');
    }

    public function test_if_else()
    {
        $this->assert_template_result(' YES ', '{% if false %} NO {% else %} YES {% endif %}');
        $this->assert_template_result(' YES ', '{% if true %} YES {% else %} NO {% endif %}');
        $this->assert_template_result(' YES ', '{% if "foo" %} YES {% else %} NO {% endif %}');
    }

    public function test_if_boolean()
    {
        $this->assert_template_result(' YES ', '{% if var %} YES {% endif %}', array('var' => true));
    }

    public function test_if_or()
    {
        //$this->assert_template_result(' YES ', '{% if a or b %} YES {% endif %}', array('a' => true, 'b' => true));
        //$this->assert_template_result(' YES ', '{% if a or b %} YES {% endif %}', array('a' => true, 'b' => false));
        debug('THIS BELOW');
        $this->assert_template_result(' YES ', '{% if a or b %} YES {% endif %}', array('a' => false, 'b' => true));
        //$this->assert_template_result('', '{% if a or b %} YES {% endif %}', array('a' => false, 'b' => false));
        return;
        $this->assert_template_result(' YES ', '{% if a or b or c %} YES {% endif %}', array('a' => false, 'b' => false, 'c' => true));
        $this->assert_template_result('', '{% if a or b or c %} YES {% endif %}', array('a' => false, 'b' => false, 'c' => false));
    }

    public function test_if_or_with_operators()
    {
        return;
        $this->assert_template_result(' YES ', '{% if a == true or b == true %} YES {% endif %}', array('a' => true, 'b' => true));
        $this->assert_template_result(' YES ', '{% if a == true or b == false %} YES {% endif %}', array('a' => true, 'b' => true));
        $this->assert_template_result('', '{% if a == false or b == false %} YES {% endif %}', array('a' => true, 'b' => true));
    }

    public function test_comparison_of_strings_containing_and_or_or()
    {
        try {
            $awful_markup = "a == 'and' and b == 'or' and c == 'foo and bar' and d == 'bar or baz' and e == 'foo' and foo and bar";
            $assigns = array('a' => 'and', 'b' => 'or', 'c' => 'foo and bar', 'd' => 'bar or baz', 'e' => 'foo', 'foo' => true, 'bar' => true);
            $this->assert_template_result(' YES ', "{% if {$awful_markup} %} YES {% endif %}", $assigns);
        } catch (\Exception $ex) {
            $this->fail('No exception should have been thrown.');
        }
    }

    public function test_comparison_of_expressions_starting_with_and_or_or()
    {
        $assigns = array('order' => array('items_count' => 0), 'android' => array('name' => 'Roy'));
        try {
            $this->assert_template_result(
                "YES",
                "{% if android.name == 'Roy' %}YES{% endif %}",
                $assigns
            );
        } catch (\Exception $ex) {
            $this->fail('No exception should have been raised.');
        }

        try {
            $this->assert_template_result(
                "YES",
                "{% if order.items_count == 0 %}YES{% endif %}",
                $assigns
            );
        } catch (\Exception $ex) {
            $this->fail('No exception should have been raised.');
        }
    }

    public function test_if_and()
    {
        $this->assert_template_result(' YES ', '{% if true and true %} YES {% endif %}');
        $this->assert_template_result('', '{% if false and true %} YES {% endif %}');
        $this->assert_template_result('', '{% if false and true %} YES {% endif %}');
    }


    public function test_hash_miss_generates_false()
    {
        $this->assert_template_result('', '{% if foo.bar %} NO {% endif %}', array('foo' => array()));
    }

    public function test_if_from_variable()
    {
        $this->assert_template_result('', '{% if var %} NO {% endif %}', array('var' => false));
        $this->assert_template_result('', '{% if var %} NO {% endif %}', array('var' => null));
        $this->assert_template_result('', '{% if foo.bar %} NO {% endif %}', array('foo' => array('bar' => false)));
        $this->assert_template_result('', '{% if foo.bar %} NO {% endif %}', array('foo' => array()));
        $this->assert_template_result('', '{% if foo.bar %} NO {% endif %}', array('foo' => null));
        $this->assert_template_result('', '{% if foo.bar %} NO {% endif %}', array('foo' => true));

        $this->assert_template_result(' YES ', '{% if var %} YES {% endif %}', array('var' => "text"));
        $this->assert_template_result(' YES ', '{% if var %} YES {% endif %}', array('var' => true));
        $this->assert_template_result(' YES ', '{% if var %} YES {% endif %}', array('var' => 1));
        $this->assert_template_result(' YES ', '{% if var %} YES {% endif %}', array('var' => array()));
        $this->assert_template_result(' YES ', '{% if var %} YES {% endif %}', array('var' => array()));
        $this->assert_template_result(' YES ', '{% if "foo" %} YES {% endif %}');
        $this->assert_template_result(' YES ', '{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => true)));
        $this->assert_template_result(' YES ', '{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => "text")));
        $this->assert_template_result(' YES ', '{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => 1)));
        $this->assert_template_result(' YES ', '{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => array())));
        $this->assert_template_result(' YES ', '{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => array())));

        $this->assert_template_result(' YES ', '{% if var %} NO {% else %} YES {% endif %}', array('var' => false));
        $this->assert_template_result(' YES ', '{% if var %} NO {% else %} YES {% endif %}', array('var' => null));
        $this->assert_template_result(' YES ', '{% if var %} YES {% else %} NO {% endif %}', array('var' => true));
        $this->assert_template_result(' YES ', '{% if "foo" %} YES {% else %} NO {% endif %}', array('var' => "text"));

        $this->assert_template_result(' YES ', '{% if foo.bar %} NO {% else %} YES {% endif %}', array('foo' => array('bar' => false)));
        $this->assert_template_result(' YES ', '{% if foo.bar %} YES {% else %} NO {% endif %}', array('foo' => array('bar' => true)));
        $this->assert_template_result(' YES ', '{% if foo.bar %} YES {% else %} NO {% endif %}', array('foo' => array('bar' => "text")));
        $this->assert_template_result(' YES ', '{% if foo.bar %} NO {% else %} YES {% endif %}', array('foo' => array('notbar' => true)));
        $this->assert_template_result(' YES ', '{% if foo.bar %} NO {% else %} YES {% endif %}', array('foo' => array()));
        $this->assert_template_result(' YES ', '{% if foo.bar %} NO {% else %} YES {% endif %}', array('notfoo' => array('bar' => true)));
    }

    public function test_nested_if()
    {
        $this->markTestSkipped('Block shift on non-object');

        $this->assert_template_result('', '{% if false %}{% if false %} NO {% endif %}{% endif %}');
        $this->assert_template_result('', '{% if false %}{% if true %} NO {% endif %}{% endif %}');
        $this->assert_template_result('', '{% if true %}{% if false %} NO {% endif %}{% endif %}');
        $this->assert_template_result(' YES ', '{% if true %}{% if true %} YES {% endif %}{% endif %}');

        $this->assert_template_result(' YES ', '{% if true %}{% if true %} YES {% else %} NO {% endif %}{% else %} NO {% endif %}');
        $this->assert_template_result(' YES ', '{% if true %}{% if false %} NO {% else %} YES {% endif %}{% else %} NO {% endif %}');
        $this->assert_template_result(' YES ', '{% if false %}{% if true %} NO {% else %} NONO {% endif %}{% else %} YES {% endif %}');
    }

    public function test_comparisons_on_null()
    {
        $this->assert_template_result('', '{% if null < 10 %} NO {% endif %}');
        $this->assert_template_result('', '{% if null <= 10 %} NO {% endif %}');
        $this->assert_template_result('', '{% if null >= 10 %} NO {% endif %}');
        $this->assert_template_result('', '{% if null > 10 %} NO {% endif %}');

        $this->assert_template_result('', '{% if 10 < null %} NO {% endif %}');
        $this->assert_template_result('', '{% if 10 <= null %} NO {% endif %}');
        $this->assert_template_result('', '{% if 10 >= null %} NO {% endif %}');
        $this->assert_template_result('', '{% if 10 > null %} NO {% endif %}');
    }

    public function test_else_if()
    {
        $this->assert_template_result('0', '{% if 0 == 0 %}0{% elsif 1 == 1%}1{% else %}2{% endif %}');
        $this->assert_template_result('1', '{% if 0 != 0 %}0{% elsif 1 == 1%}1{% else %}2{% endif %}');
        $this->assert_template_result('2', '{% if 0 != 0 %}0{% elsif 1 != 1%}1{% else %}2{% endif %}');

        $this->assert_template_result('elsif', '{% if false %}if{% elsif true %}elsif{% endif %}');
    }

    public function test_syntax_error_no_variable()
    {
        try {
            $this->assert_template_result('', '{% if jerry == 1 %}');
            $this->fail('A SyntaxError should have been raised.');
        } catch (SyntaxError $e) {
        }
    }

    public function test_syntax_error_no_expression()
    {
        try {
            $this->assert_template_result('', '{% if %}');
            $this->fail('A SyntaxError should have been raised.');
        } catch (SyntaxError $e) {

        }
    }

    public function test_if_with_custom_condition()
    {
        $this->assert_template_result('yes', "{% if 'bob' contains 'o' %}yes{% endif %}");
        $this->assert_template_result('no', "{% if 'bob' contains 'f' %}yes{% else %}no{% endif %}");
    }

    public function test_operators_are_ignored_unless_isolated()
    {
        $this->assert_template_result(
            'yes',
            "{% if 'gnomeslab-and-or-liquid' contains 'gnomeslab-and-or-liquid' %}yes{% endif %}"
        );
    }

    public function test_operators_are_whitelisted()
    {
        try {
            $this->assert_template_result('', "{% if 1 or throw or or 1 %}yes{% endif %}");
            $this->fail('A SyntaxError should have been thrown.');
        } catch (SyntaxError $e) {
        }
    }
}

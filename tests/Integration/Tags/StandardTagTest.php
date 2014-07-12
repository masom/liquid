<?php


namespace Liquid\Tests\Integration\Tags;


use Liquid\Exceptions\SyntaxError;
use Liquid\Template;
use Liquid\Tests\IntegrationTestCase;


class StandardTagTest extends IntegrationTestCase {
    public function test_no_transform() {
        $this->assert_template_result(
            'this text should come out of the template without change...',
            'this text should come out of the template without change...'
        );

        $this->assert_template_result('blah','blah');
        $this->assert_template_result('<blah>','<blah>');
        $this->assert_template_result('|,.:','|,.:');
        $this->assert_template_result('','');

        $text = <<<EOT
this shouldnt see any transformation either but has multiple lines
as you can clearly see here ...
EOT;
        $this->assert_template_result($text, $text);
    }

    public function test_has_a_block_which_does_nothing() {
        $this->markTestSkipped('Block shift problem');
        $this->assert_template_result(
            'the comment block should be removed  .. right?',
            'the comment block should be removed {%comment%} be gone.. {%endcomment%} .. right?'
        );

        $this->assert_template_result('','{%comment%}{%endcomment%}');
        $this->assert_template_result('','{%comment%}{% endcomment %}');
        $this->assert_template_result('','{% comment %}{%endcomment%}');
        $this->assert_template_result('','{% comment %}{% endcomment %}');
        $this->assert_template_result('','{%comment%}comment{%endcomment%}');
        $this->assert_template_result('','{% comment %}comment{% endcomment %}');
        $this->assert_template_result('','{% comment %} 1 {% comment %} 2 {% endcomment %} 3 {% endcomment %}');

        $this->assert_template_result('','{%comment%}{%blabla%}{%endcomment%}');
        $this->assert_template_result('','{% comment %}{% blabla %}{% endcomment %}');
        $this->assert_template_result('','{%comment%}{% endif %}{%endcomment%}');
        $this->assert_template_result('','{% comment %}{% endwhatever %}{% endcomment %}');
        $this->assert_template_result('','{% comment %}{% raw %} {{%%%%}}  }} { {% endcomment %} {% comment {% endraw %} {% endcomment %}');

        $this->assert_template_result('foobar','foo{%comment%}comment{%endcomment%}bar');
        $this->assert_template_result('foobar','foo{% comment %}comment{% endcomment %}bar');
        $this->assert_template_result('foobar','foo{%comment%} comment {%endcomment%}bar');
        $this->assert_template_result('foobar','foo{% comment %} comment {% endcomment %}bar');

        $this->assert_template_result('foo  bar','foo {%comment%} {%endcomment%} bar');
        $this->assert_template_result('foo  bar','foo {%comment%}comment{%endcomment%} bar');
        $this->assert_template_result('foo  bar','foo {%comment%} comment {%endcomment%} bar');

        $this->assert_template_result('foobar','foo{%comment%}
                                     {%endcomment%}bar');
    }

    public function test_hyphenated_assign() {
        $assigns = array('a-b' => '1');
        $this->assert_template_result('a-b:1 a-b:2', 'a-b:{{a-b}} {%assign a-b = 2 %}a-b:{{a-b}}', $assigns);
    }

    public function test_assign_with_colon_and_spaces() {
        $assigns = array('var' => array('a:b c' => array('paged' => '1')));
        $this->assert_template_result('var2: 1', '{%assign var2 = var["a:b c"].paged %}var2: {{var2}}', $assigns);
    }

    public function test_capture() {
        $assigns = array('var' => 'content');
        $this->assert_template_result(
            'content foo content foo ',
            '{{ var2 }}{% capture var2 %}{{ var }} foo {% endcapture %}{{ var2 }}{{ var2 }}',
            $assigns
        );
    }

    public function test_capture_detects_bad_syntax() {
        try {
            $this->assert_template_result(
                'content foo content foo ',
                '{{ var2 }}{% capture %}{{ var }} foo {% endcapture %}{{ var2 }}{{ var2 }}',
                array('var' => 'content')
            );
            $this->fail('A SyntaxError should have been thrown.');
        } catch( SyntaxError $e ){
        }
    }

    public function test_case() {
        $assigns = array('condition' => 2);
        $this->assert_template_result(
            ' its 2 ',
            '{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}',
            $assigns
        );

        $assigns = array('condition' => 1);
        $this->assert_template_result(
            ' its 1 ',
            '{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}',
            $assigns
        );

        $assigns = array('condition' => 3);
        $this->assert_template_result(
            '',
            '{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}',
            $assigns
        );

        $assigns = array('condition' => "string here");
        $this->assert_template_result(
            ' hit ',
            '{% case condition %}{% when "string here" %} hit {% endcase %}',
            $assigns
        );

        $assigns = array('condition' => "bad string here");
        $this->assert_template_result(
            '',
            '{% case condition %}{% when "string here" %} hit {% endcase %}',
            $assigns
        );
    }

    public function test_case_with_else() {
        $assigns = array('condition' => 5);
        $this->assert_template_result(
            ' hit ',
            '{% case condition %}{% when 5 %} hit {% else %} else {% endcase %}',
            $assigns
        );

        $assigns = array('condition' => 6);
        $this->assert_template_result(
            ' else ',
            '{% case condition %}{% when 5 %} hit {% else %} else {% endcase %}',
            $assigns
        );

        $assigns = array('condition' => 6);
        $this->assert_template_result(
            ' else ',
            '{% case condition %} {% when 5 %} hit {% else %} else {% endcase %}',
            $assigns
        );
  }

    public function test_case_on_size() {
        $this->assert_template_result('',  '{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', array('a' => array()));
        $this->assert_template_result('1', '{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', array('a' => array(1)));
        $this->assert_template_result('2', '{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', array('a' => array(1, 1)));
        $this->assert_template_result('',  '{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', array('a' => array(1, 1, 1)));
        $this->assert_template_result('',  '{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', array('a' => array(1, 1, 1, 1)));
        $this->assert_template_result('',  '{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', array('a' => array(1, 1, 1, 1, 1)));
    }

    public function test_case_on_size_with_else() {

        $this->assert_template_result('else',
            '{% case a.size %}{% when 1 %}1{% when 2 %}2{% else %}else{% endcase %}',
            array('a' => array())
        );

        $this->assert_template_result('1',
            '{% case a.size %}{% when 1 %}1{% when 2 %}2{% else %}else{% endcase %}',
            array('a' => array(1))
        );

        $this->assert_template_result('2',
            '{% case a.size %}{% when 1 %}1{% when 2 %}2{% else %}else{% endcase %}',
            array('a' => array(1, 1))
        );

        $this->assert_template_result('else',
            '{% case a.size %}{% when 1 %}1{% when 2 %}2{% else %}else{% endcase %}',
            array('a' => array(1, 1, 1))
        );

        $this->assert_template_result('else',
            '{% case a.size %}{% when 1 %}1{% when 2 %}2{% else %}else{% endcase %}',
            array('a' => array(1, 1, 1, 1))
        );

        $this->assert_template_result('else',
            '{% case a.size %}{% when 1 %}1{% when 2 %}2{% else %}else{% endcase %}',
            array('a' => array(1, 1, 1, 1, 1))
        );
    }

    public function test_case_on_length_with_else() {
        $this->assert_template_result(
            'else',
            '{% case a.empty? %}{% when true %}true{% when false %}false{% else %}else{% endcase %}',
            array()
        );

        $this->assert_template_result(
            'false',
            '{% case false %}{% when true %}true{% when false %}false{% else %}else{% endcase %}',
            array()
        );

        $this->assert_template_result(
            'true',
            '{% case true %}{% when true %}true{% when false %}false{% else %}else{% endcase %}',
            array()
        );

        $this->assert_template_result(
            'else',
            '{% case NULL %}{% when true %}true{% when false %}false{% else %}else{% endcase %}',
            array()
        );
    }

    public function test_assign_from_case() {
        # Example from the shopify forums
        $code = "{% case collection.handle %}{% when 'menswear-jackets' %}{% assign ptitle = 'menswear' %}{% when 'menswear-t-shirts' %}{% assign ptitle = 'menswear' %}{% else %}{% assign ptitle = 'womenswear' %}{% endcase %}{{ ptitle }}";
        /** @var Template $template */
        $template = Template::parse($code);
        $this->assertEquals("menswear",   $template->render(array("collection" => array('handle' => 'menswear-jackets'))));
        $this->assertEquals("menswear",   $template->render(array("collection" => array('handle' => 'menswear-t-shirts'))));
        $this->assertEquals("womenswear", $template->render(array("collection" => array('handle' => 'x'))));
        $this->assertEquals("womenswear", $template->render(array("collection" => array('handle' => 'y'))));
        $this->assertEquals("womenswear", $template->render(array("collection" => array('handle' => 'z'))));
    }

    public function test_case_when_or() {
        $code = '{% case condition %}{% when 1 or 2 or 3 %} its 1 or 2 or 3 {% when 4 %} its 4 {% endcase %}';
        $this->assert_template_result(' its 1 or 2 or 3 ', $code, array('condition' => 1));
        $this->assert_template_result(' its 1 or 2 or 3 ', $code, array('condition' => 2));
        $this->assert_template_result(' its 1 or 2 or 3 ', $code, array('condition' => 3));
        $this->assert_template_result(' its 4 ', $code, array('condition' => 4));
        $this->assert_template_result('', $code, array('condition' => 5));

        $code = '{% case condition %}{% when 1 or "string" or null %} its 1 or 2 or 3 {% when 4 %} its 4 {% endcase %}';
        $this->assert_template_result(' its 1 or 2 or 3 ', $code, array('condition' => 1));
        $this->assert_template_result(' its 1 or 2 or 3 ', $code, array('condition' => 'string'));
        $this->assert_template_result(' its 1 or 2 or 3 ', $code, array('condition' => null));
        $this->assert_template_result('', $code, array('condition' => 'something else'));
    }

    public function test_case_when_comma() {
        $code = '{% case condition %}{% when 1, 2, 3 %} its 1 or 2 or 3 {% when 4 %} its 4 {% endcase %}';
        $this->assert_template_result(' its 1 or 2 or 3 ', $code, array('condition' => 1));
        $this->assert_template_result(' its 1 or 2 or 3 ', $code, array('condition' => 2));
        $this->assert_template_result(' its 1 or 2 or 3 ', $code, array('condition' => 3));
        $this->assert_template_result(' its 4 ', $code, array('condition' => 4));
        $this->assert_template_result('', $code, array('condition' => 5));

        $code = '{% case condition %}{% when 1, "string", null %} its 1 or 2 or 3 {% when 4 %} its 4 {% endcase %}';
        $this->assert_template_result(' its 1 or 2 or 3 ', $code, array('condition' => 1));
        $this->assert_template_result(' its 1 or 2 or 3 ', $code, array('condition' => 'string'));
        $this->assert_template_result(' its 1 or 2 or 3 ', $code, array('condition' => null));
        $this->assert_template_result('', $code, array('condition' => 'something else'));
    }

    public function test_assign() {
        $this->assert_template_result('variable', '{% assign a = "variable"%}{{a}}');
    }

    public function test_assign_unassigned() {
        $assigns = array('var' => 'content');
        $this->assert_template_result('var2:  var2:content', 'var2:{{var2}} {%assign var2 = var%} var2:{{var2}}', $assigns);
    }

    public function test_assign_an_empty_string() {
        $this->assert_template_result('', '{% assign a = ""%}{{a}}');
    }

    public function test_assign_is_global() {
        $this->assert_template_result('variable', '{%for i in (1..2) %}{% assign a = "variable"%}{% endfor %}{{a}}');
    }

    public function test_case_detects_bad_syntax() {
        try{
            $this->assert_template_result('',  '{% case false %}{% when %}true{% endcase %}', array());
            $this->fail('A SyntaxError should have been thrown');
        } catch(SyntaxError $e) {}
        try{
            $this->assert_template_result('',  '{% case false %}{% huh %}true{% endcase %}', array());
        } catch(SyntaxError $e) {}
    }

    public function test_cycle() {
        $this->markTestSkipped('Cycle seems broken');
        $this->assert_template_result('one','{%cycle "one", "two"%}');
        $this->assert_template_result('one two','{%cycle "one", "two"%} {%cycle "one", "two"%}');
        $this->assert_template_result(' two','{%cycle "", "two"%} {%cycle "", "two"%}');

        $this->assert_template_result('one two one','{%cycle "one", "two"%} {%cycle "one", "two"%} {%cycle "one", "two"%}');

        $this->assert_template_result(
            'text-align: left text-align: right',
            '{%cycle "text-align: left", "text-align: right" %} {%cycle "text-align: left", "text-align: right"%}'
        );
    }

    public function test_multiple_cycles() {
        $this->markTestSkipped('Cycle seems broken');
        $this->assert_template_result(
            '1 2 1 1 2 3 1',
            '{%cycle 1,2%} {%cycle 1,2%} {%cycle 1,2%} {%cycle 1,2,3%} {%cycle 1,2,3%} {%cycle 1,2,3%} {%cycle 1,2,3%}'
        );
    }

    public function test_multiple_named_cycles() {
        $this->markTestSkipped('Cycle seems broken');
        $this->assert_template_result(
            'one one two two one one',
            '{%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %} {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %} {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %}'
        );
    }

    public function test_multiple_named_cycles_with_names_from_context() {
        $this->markTestSkipped('Cycle seems broken');
        $assigns = array("var1" => 1, "var2" => 2);
        $this->assert_template_result(
            'one one two two one one',
            '{%cycle var1: "one", "two" %} {%cycle var2: "one", "two" %} {%cycle var1: "one", "two" %} {%cycle var2: "one", "two" %} {%cycle var1: "one", "two" %} {%cycle var2: "one", "two" %}',
            $assigns
        );
    }

    public function test_size_of_array() {
        $assigns = array("array" => array(1,2,3,4));
        $this->assert_template_result('array has 4 elements', "array has {{ array.size }} elements", $assigns);
    }

    public function test_size_of_hash() {
        $assigns = array("hash" => array('a' => 1, 'b' => 2, 'c'=> 3, 'd' => 4));
        $this->assert_template_result('hash has 4 elements', "hash has {{ hash.size }} elements", $assigns);
    }

    public function test_illegal_symbols() {
        $this->assert_template_result('', '{% if true == empty %}?{% endif %}', array());
        $this->assert_template_result('', '{% if true == null %}?{% endif %}', array());
        $this->assert_template_result('', '{% if empty == true %}?{% endif %}', array());
        $this->assert_template_result('', '{% if null == true %}?{% endif %}', array());
    }

    public function test_ifchanged() {
        $assigns = array('array' => array(1, 1, 2, 2, 3, 3));
        $this->assert_template_result(
            '123',
            '{%for item in array%}{%ifchanged%}{{item}}{% endifchanged %}{%endfor%}',
            $assigns
        );

        $assigns = array('array' => array(1, 1, 1, 1));
        $this->assert_template_result(
            '1',
            '{%for item in array%}{%ifchanged%}{{item}}{% endifchanged %}{%endfor%}',
            $assigns
        );
    }

    public function test_multiline_tag() {
        $this->assert_template_result('0 1 2 3', "0{%\nfor i in (1..3)\n%} {{\ni\n}}{%\nendfor\n%}");
    }
}

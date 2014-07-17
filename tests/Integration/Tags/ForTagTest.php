<?php


namespace Liquid\Tests\Integration\Tags;


use Liquid\Exceptions\SyntaxError;
use Liquid\Template;
use Liquid\Tests\IntegrationTestCase;
use Liquid\Tests\Lib\LoaderDrop;
use Liquid\Tests\Lib\ThingWithValue;


class ForTagTest extends IntegrationTestCase {

    public function test_for() {
        $this->assert_template_result(' yo  yo  yo  yo ','{%for item in array%} yo {%endfor%}', array('array' => array(1,2,3,4)));
        $this->assert_template_result('yoyo','{%for item in array%}yo{%endfor%}', array('array' => array(1,2)));
        $this->assert_template_result(' yo ','{%for item in array%} yo {%endfor%}', array('array' => array(1)));
        $this->assert_template_result('','{%for item in array%}{%endfor%}', array('array' => array(1,2)));
        $expected = <<<HERE

        yo

        yo

        yo

HERE;

        $template = <<<HERE
        {%for item in array%}
        yo
        {%endfor%}
HERE;
            $this->assert_template_result($expected, $template, array('array' => array(1,2,3)));
          }

    public function test_for_reversed() {
        $assigns = array('array' => array(1, 2, 3));
        $this->assert_template_result(
            '321','{%for item in array reversed %}{{item}}{%endfor%}', $assigns);
    }

    public function test_for_with_range() {
        $this->assert_template_result(' 1  2  3 ','{%for item in (1..3) %} {{item}} {%endfor%}');
    }

    public function test_for_with_variable_range() {
        $this->assert_template_result(
            ' 1  2  3 ',
            '{%for item in (1..foobar) %} {{item}} {%endfor%}',
            array("foobar" => 3)
        );
    }

    public function test_for_with_hash_value_range() {
        $foobar = array( "value" => 3);
        $this->assert_template_result(
            ' 1  2  3 ',
            '{%for item in (1..foobar.value) %} {{item}} {%endfor%}',
            array("foobar" => $foobar)
        );
    }

    public function test_for_with_drop_value_range() {
        $foobar = new ThingWithValue();
        $this->assert_template_result(
            ' 1  2  3 ',
            '{%for item in (1..foobar.value) %} {{item}} {%endfor%}',
            array("foobar" => $foobar)
        );
    }

    public function test_for_with_variable() {
        $this->assert_template_result(' 1  2  3 ','{%for item in array%} {{item}} {%endfor%}', array('array' => array(1,2,3)));
        $this->assert_template_result('123','{%for item in array%}{{item}}{%endfor%}', array('array' => array(1,2,3)));
        $this->assert_template_result('123','{% for item in array %}{{item}}{% endfor %}',array('array' => array(1,2,3)));
        $this->assert_template_result('abcd','{%for item in array%}{{item}}{%endfor%}',array('array' => array('a','b','c','d')));
        $this->assert_template_result('a b c','{%for item in array%}{{item}}{%endfor%}',array('array' => array('a',' ','b',' ','c')));
        $this->assert_template_result('abc','{%for item in array%}{{item}}{%endfor%}',array('array' => array('a','','b','','c')));
    }

    public function test_for_helpers() {
        $assigns = array('array' => array(1,2,3));

        $this->assert_template_result(' 1/3  2/3  3/3 ',
            '{%for item in array%} {{forloop.index}}/{{forloop.length}} {%endfor%}',
            $assigns
        );

        $this->assert_template_result(' 1  2  3 ', '{%for item in array%} {{forloop.index}} {%endfor%}', $assigns);
        $this->assert_template_result(' 0  1  2 ', '{%for item in array%} {{forloop.index0}} {%endfor%}', $assigns);
        $this->assert_template_result(' 2  1  0 ', '{%for item in array%} {{forloop.rindex0}} {%endfor%}', $assigns);
        $this->assert_template_result(' 3  2  1 ', '{%for item in array%} {{forloop.rindex}} {%endfor%}', $assigns);
        $this->assert_template_result(' true  false  false ', '{%for item in array%} {{forloop.first}} {%endfor%}', $assigns);
        $this->assert_template_result(' false  false  true ', '{%for item in array%} {{forloop.last}} {%endfor%}', $assigns);
    }

    public function test_for_and_if() {
        $assigns = array('array' => array(1,2,3));
        $this->assert_template_result(
            '+--',
            '{%for item in array%}{% if forloop.first %}+{% else %}-{% endif %}{%endfor%}',
            $assigns
        );
    }

    public function test_for_else() {
        $this->assert_template_result('+++', '{%for item in array%}+{%else%}-{%endfor%}', array('array' => array(1,2,3)));
        $this->assert_template_result('-',   '{%for item in array%}+{%else%}-{%endfor%}', array('array' => array()));
        $this->assert_template_result('-',   '{%for item in array%}+{%else%}-{%endfor%}', array('array' => null));
    }

    public function test_limiting() {
        $assigns = array('array' => array(1,2,3,4,5,6,7,8,9,0));
        $this->assert_template_result('12', '{%for i in array limit:2 %}{{ i }}{%endfor%}', $assigns);
        $this->assert_template_result('1234', '{%for i in array limit:4 %}{{ i }}{%endfor%}', $assigns);
        $this->assert_template_result('3456', '{%for i in array limit:4 offset:2 %}{{ i }}{%endfor%}', $assigns);
        $this->assert_template_result('3456', '{%for i in array limit: 4 offset: 2 %}{{ i }}{%endfor%}', $assigns);
    }

    public function test_dynamic_variable_limiting() {
        $assigns = array('array' => array(1,2,3,4,5,6,7,8,9,0));
        $assigns['limit'] = 2;
        $assigns['offset'] = 2;

        $this->assert_template_result('34', '{%for i in array limit: limit offset: offset %}{{ i }}{%endfor%}', $assigns);
    }

    public function test_nested_for() {
        $this->markTestSkipped('Block shift on non-object');
        $assigns = array('array' => array(array(1,2), array(3,4), array(5,6)));
        $this->assert_template_result('123456', '{%for item in array%}{%for i in item%}{{ i }}{%endfor%}{%endfor%}', $assigns);
    }

    public function test_offset_only() {
        $assigns = array('array' => array(1,2,3,4,5,6,7,8,9,0));
        $this->assert_template_result('890', '{%for i in array offset:7 %}{{ i }}{%endfor%}', $assigns);
    }

    public function test_pause_resume() {
        $assigns = array('array' => array('items' => array(1,2,3,4,5,6,7,8,9,0)));
        $markup = <<<MKUP
          {%for i in array.items limit: 3 %}{{i}}{%endfor%}
          next
          {%for i in array.items offset:continue limit: 3 %}{{i}}{%endfor%}
          next
          {%for i in array.items offset:continue limit: 3 %}{{i}}{%endfor%}
MKUP;
        $expected = <<<XPCTD
          123
          next
          456
          next
          789
XPCTD;

        $this->assert_template_result($expected, $markup, $assigns);
    }

    public function test_pause_resume_limit() {
        $assigns = array('array' => array('items' => array(1,2,3,4,5,6,7,8,9,0)));
        $markup = <<<MKUP
          {%for i in array.items limit:3 %}{{i}}{%endfor%}
          next
          {%for i in array.items offset:continue limit:3 %}{{i}}{%endfor%}
          next
          {%for i in array.items offset:continue limit:1 %}{{i}}{%endfor%}
MKUP;
        $expected = <<<XPCTD
          123
          next
          456
          next
          7
XPCTD;
        $this->assert_template_result($expected, $markup, $assigns);
    }

    public function test_pause_resume_BIG_limit() {
        $assigns = array('array' => array('items' => array(1,2,3,4,5,6,7,8,9,0)));
        $markup = <<<MKUP
          {%for i in array.items limit:3 %}{{i}}{%endfor%}
          next
          {%for i in array.items offset:continue limit:3 %}{{i}}{%endfor%}
          next
          {%for i in array.items offset:continue limit:1000 %}{{i}}{%endfor%}
MKUP;
        $expected = <<<XPCTD
          123
          next
          456
          next
          7890
XPCTD;
          $this->assert_template_result($expected, $markup, $assigns);
    }


    public function test_pause_resume_BIG_offset() {
        $assigns = array('array' => array('items' => array(1,2,3,4,5,6,7,8,9,0)));
        $markup = '{%for i in array.items limit:3 %}{{i}}{%endfor%}
          next
          {%for i in array.items offset:continue limit:3 %}{{i}}{%endfor%}
          next
          {%for i in array.items offset:continue limit:3 offset:1000 %}{{i}}{%endfor%}';
        $expected = '123
          next
          456
          next
          ';
          $this->assert_template_result($expected, $markup, $assigns);
    }

    public function test_for_with_break() {
        $this->markTestSkipped('Block shift non object');
        $assigns = array('array' => array('items' => array(1,2,3,4,5,6,7,8,9,10)));

        $markup = '{% for i in array.items %}{% break %}{% endfor %}';
        $expected = "";
        $this->assert_template_result($expected, $markup, $assigns);

        $markup = '{% for i in array.items %}{{ i }}{% break %}{% endfor %}';
        $expected = "1";
        $this->assert_template_result($expected, $markup, $assigns);

        $markup = '{% for i in array.items %}{% break %}{{ i }}{% endfor %}';
        $expected = "";
        $this->assert_template_result($expected, $markup, $assigns);

        $markup = '{% for i in array.items %}{{ i }}{% if i > 3 %}{% break %}{% endif %}{% endfor %}';
        $expected = "1234";
        $this->assert_template_result($expected, $markup, $assigns);

        # tests to ensure it only breaks out of the local for loop
        # and not all of them.
        $assigns = array('array' => array(array(1,2), array(3,4), array(5,6)));
        $markup = '{% for item in array %}' .
            '{% for i in item %}' .
            '{% if i == 1 %}' .
            '{% break %}' .
            '{% endif %}' .
            '{(array( i }}' .
            '{% endfor %}' .
            '{% endfor %}';

        $expected = '3456';
        $this->assert_template_result($expected, $markup, $assigns);

        # test break does nothing when unreached
        $assigns = array('array' => array('items' => array(1,2,3,4,5)));
        $markup = '{% for i in array.items %}{% if i == 9999 %}{% break %}{% endif %}{{ i }}{% endfor %}';
        $expected = '12345';
        $this->assert_template_result($expected, $markup, $assigns);
    }

    public function test_for_with_continue() {
        $this->markTestSkipped('Block shift non object');
        $assigns = array('array' => array('items' => array(1,2,3,4,5)));

        $markup = '{% for i in array.items %}{% continue %}{% endfor %}';
        $expected = "";
        $this->assert_template_result($expected, $markup, $assigns);

        $markup = '{% for i in array.items %}{{ i }}{% continue %}{% endfor %}';
        $expected = "12345";
        $this->assert_template_result($expected, $markup, $assigns);

        $markup = '{% for i in array.items %}{% continue %}{{ i }}{% endfor %}';
        $expected = "";
        $this->assert_template_result($expected, $markup, $assigns);

        $markup = '{% for i in array.items %}{% if i > 3 %}{% continue %}{% endif %}{{ i }}{% endfor %}';
        $expected = "123";
        $this->assert_template_result($expected, $markup, $assigns);

        $markup = '{% for i in array.items %}{% if i == 3 %}{% continue %}{% else %}{{ i }}{% endif %}{% endfor %}';
        $expected = "1245";
        $this->assert_template_result($expected, $markup, $assigns);

        # tests to ensure it only continues the local for loop and not all of them.
        $assigns = array('array' => array(array(1,2), array(3,4), array(5,6)));
        $markup = '{% for item in array %}' .
            '{% for i in item %}' .
        '{% if i == 1 %}' .
        '{% continue %}' .
        '{% endif %}' .
        '{(array( i }}' .
        '{% endfor %}' .
        '{% endfor %}';
        $expected = '23456';
        $this->assert_template_result($expected, $markup, $assigns);

        # test continue does nothing when unreached
        $assigns = array('array' => array('items' => array(1,2,3,4,5)));
        $markup = '{% for i in array.items %}{% if i == 9999 %}{% continue %}{% endif %}{{ i }}{% endfor %}';
        $expected = '12345';
        $this->assert_template_result($expected, $markup, $assigns);
    }

    public function test_for_tag_string() {
        # ruby 1.8.7 "String".each => Enumerator with single "String" element.
        # ruby 1.9.3 no longer supports .each on String though we mimic
        # the functionality for backwards compatibility

        $this->assert_template_result(
            'test string',
            '{%for val in string%}array(array(val}}{%endfor%}',
            array('string' => "test string")
        );

        $this->assert_template_result(
            'test string',
            '{%for val in string limit:1%}array(array(val}}{%endfor%}',
            array('string' => "test string")
        );

        $this->assert_template_result(
            'val-string-1-1-0-1-0-true-true-test string',
            '{%for val in string%}' .
            '{(array(forloop.name}}-' .
            '{(array(forloop.index}}-' .
            '{(array(forloop.length}}-' .
            '{(array(forloop.index0}}-' .
            '{(array(forloop.rindex}}-' .
            '{(array(forloop.rindex0}}-' .
            '{(array(forloop.first}}-' .
            '{(array(forloop.last}}-' .
            '{(array(val}}{%endfor%}',
            array('string' => "test string")
        );
    }

    public function test_blank_string_not_iterable() {
        $this->assert_template_result('', "{% for char in characters %}I WILL NOT BE OUTPUT{% endfor %}", array('characters' => ''));
    }

    public function test_bad_variable_naming_in_for_loop() {
        try {
            Template::parse('{% for a/b in x %}{% endfor %}');
            $this->fail('A SyntaxError should have been thrown.');
        } catch(SyntaxError $e){
        }
    }

    public function test_spacing_with_variable_naming_in_for_loop() {
        $expected = '12345';
        $template = '{% for       item   in   items %}{{item}}{% endfor %}';
        $assigns  = array('items' => array(1,2,3,4,5));
        $this->assert_template_result($expected, $template, $assigns);
    }

    public function test_iterate_with_each_when_no_limit_applied() {
        $loader = new LoaderDrop(array(1,2,3,4,5));
        $assigns = array('items' => $loader);
        $expected = '12345';
        $template = '{% for item in items %}{{item}}{% endfor %}';
        $this->assert_template_result($expected, $template, $assigns);
        $this->assertTrue($loader->each_called());
        $this->assertFalse($loader->load_slice_called());
    }

    public function test_iterate_with_load_slice_when_limit_applied() {
        $loader = new  LoaderDrop(array(1,2,3,4,5));
        $assigns = array('items' => $loader);
        $expected = '1';
        $template = '{% for item in items limit:1 %}{{item}}{% endfor %}';
        $this->assert_template_result($expected, $template, $assigns);
        $this->assertFalse($loader->each_called());
        $this->assertTrue($loader->load_slice_called());
    }

    public function test_iterate_with_load_slice_when_limit_and_offset_applied() {
        $loader = new LoaderDrop(array(1,2,3,4,5));
        $assigns = array('items' => $loader);
        $expected = '34';
        $template = '{% for item in items offset:2 limit:2 %}{{item}}{% endfor %}';
        $this->assert_template_result($expected, $template, $assigns);
        $this->assertFalse($loader->each_called());
        $this->assertTrue($loader->load_slice_called());
    }

    public function test_iterate_with_load_slice_returns_same_results_as_without() {
        $loader = new LoaderDrop(array(1,2,3,4,5));
        $loader_assigns = array('items' => $loader);
        $array_assigns = array('items' => array(1,2,3,4,5));
        $expected = '34';
        $template = '{% for item in items offset:2 limit:2 %}{{item}}{% endfor %}';
        $this->assert_template_result($expected, $template, $loader_assigns);
        $this->assert_template_result($expected, $template, $array_assigns);
    }
}

<?php

namespace Liquid\Tests\Unit;

use \Liquid\Context;
use \Liquid\Strainer;

use \Liquid\Tests\Lib\Category;
use \Liquid\Tests\Lib\CategoryDrop;
use \Liquid\Tests\Lib\ContextFilter;
use \Liquid\Tests\Lib\CentsDrop;
use \Liquid\Tests\Lib\CounterDrop;
use \Liquid\Tests\Lib\ContextSensitiveDrop;
use \Liquid\Tests\Lib\HundredCentes;
use \Liquid\Tests\Lib\ProcAsVariable;
use Liquid\Utils\ArrayObject;


class ContextTest extends \Liquid\Tests\TestCase {

    /** @var Context */
    protected $context;

    protected function setUp() {
        Strainer::init();
        $this->context = new Context();
    }

    protected function tearDown() {
        unset($this->context);
    }

    public function test_variables() {
        $this->context['string'] = 'string';
        $this->assertEquals('string', $this->context['string']);

        $this->context['num'] = 5;
        $this->assertEquals(5, $this->context['num']);

        $this->context['time'] = new \DateTime('2006-06-06 12:00:00', new \DateTimeZone('UTC'));
        $expected = new \DateTime('2006-06-06 12:00:00', new \DateTimeZone('UTC'));
        $this->assertEquals($expected->getTimestamp(), $this->context['time']->getTimestamp());

        $this->context['date'] = gmdate('Y-m-d');
        $this->assertEquals(gmdate('Y-m-d'), $this->context['date']);

        $this->context['bool'] = true;
        $this->assertTrue($this->context['bool']);

        $this->context['bool'] = false;
        $this->assertFalse($this->context['bool']);

        $this->context['null'] = null;
        $this->assertNull($this->context['null']);
        $this->assertNull($this->context['null']);
    }

    public function test_variables_not_existing() {
        $this->assertNull($this->context['does_not_exists']);
    }

    public function test_scoping() {
        $this->context->push();
        $this->context->pop();

        try {
            $this->context->pop();
            $this->fail('A ContextError should have been raised.');
        } catch(\Liquid\Exceptions\ContextError $e) {
        }

        try {
            $this->context->push();
            $this->context->pop();
            $this->context->pop();
            $this->fail('A ContextError should have been raised.');
        } catch(\Liquid\Exceptions\ContextError $e) {
        }
    }

    public function test_length_query() {
        $this->context['numbers'] = array(1,2,3,4);
        $this->assertEquals(4, $this->context['numbers.size']);
    }

    public function test_hyphenated_variable() {
        $this->context['oh-my'] = 'godz';
        $this->assertEquals('godz', $this->context['oh-my']);
    }

    public function test_add_filter() {
        $filter = new ContextFilter();

        $context = new Context();
        $context->add_filters($filter);
        $this->assertEquals('hi? hi!', $context->invoke('hi', 'hi?'));
    }

    public function test_only_intended_filters_make_it_here() {
        $filter = new ContextFilter();

        $context = new Context();
        $this->assertEquals('Wookie', $context->invoke('hi', 'Wookie'));

        $context->add_filters($filter);
        $this->assertEquals('Wookie hi!', $context->invoke('hi', 'Wookie'));
    }

    public function test_add_item_in_outer_scope() {
        $this->context['test'] = 'test';
        $this->context->push();
        $this->assertEquals('test', $this->context['test']);

        $this->context->pop();
        $this->assertEquals('test', $this->context['test']);
    }

    public function test_add_item_in_inner_scope() {
        $this->context->push();
        $this->context['test'] = 'test';

        $this->assertEquals('test', $this->context['test']);

        $this->context->pop();

        $this->assertNull($this->context['test']);
    }

    public function test_hierachical_data() {
        $this->context['hash'] = array('name' => 'tobi');

        $this->assertEquals('tobi', $this->context['hash.name']);
        $this->assertEquals('tobi', $this->context["hash['name']"]);
    }

    public function test_keywords() {
        $this->assertTrue($this->context['true']);
        $this->assertFalse($this->context['false']);
    }

    public function test_digits() {
        $this->assertEquals(100, $this->context['100']);
        $this->assertEquals(100.00, $this->context['100.00']);
    }

    public function test_strings() {
        $this->assertEquals('hello!', $this->context['"hello!"']);
        $this->assertEquals('hello!', $this->context["'hello!'"]);
    }

    public function test_merge() {
        $this->context->merge(array('test' => 'test'));
        $this->assertEquals('test', $this->context['test']);

        $this->context->merge(array('test' => 'newvalue', 'foo' => 'bar'));
        $this->assertEquals('newvalue', $this->context['test']);
        $this->assertEquals('bar', $this->context['foo']);
    }

    public function test_array_notation() {
        $this->context['test'] = array(1,2,3,4,5);
        $this->assertEquals(1, $this->context['test[0]']);
        $this->assertEquals(2, $this->context['test[1]']);
        $this->assertEquals(3, $this->context['test[2]']);
        $this->assertEquals(4, $this->context['test[3]']);
        $this->assertEquals(5, $this->context['test[4]']);
    }

    public function test_recoursive_array_notation() {
        $this->context['test'] = array('test' => array(1,2,3,4,5));
        $this->assertEquals(1, $this->context['test.test[0]']);

        $this->context['test'] = array(array('test' => 'worked'));
        $this->assertEquals('worked', $this->context['test[0].test']);
    }

    public function test_hash_to_array_transition() {
        $this->context['colors'] = array(
            'Blue' => array('003366','336699', '6699CC', '99CCFF'),
            'Green' => array('003300','336633', '669966', '99CC99'),
            'Yellow' => array('CC9900','FFCC00', 'FFFF99', 'FFFFCC'),
            'Red' => array('660000','993333', 'CC6666', 'FF9999')
        );

        $this->assertEquals('003366', $this->context['colors.Blue[0]']);
        $this->assertEquals('FF9999', $this->context['colors.Red[3]']);
    }

    public function test_try_first() {
        $this->context['test'] = array(1,2,3,4,5);

        $this->assertEquals(1, $this->context['test.first']);
        $this->assertEquals(5, $this->context['test.last']);

        $this->context['test'] = array('test' => array(1,2,3,4,5));
        $this->assertEquals(1, $this->context['test.test.first']);
        $this->assertEquals(5, $this->context['test.test.last']);

        $this->context['test'] = array(1);
        $this->assertEquals(1, $this->context['test.first']);
        $this->assertEquals(1, $this->context['test.last']);
    }

    public function test_access_hashes_with_hash_notation() {
        $this->context['products'] = array('count' => 5, 'tags' => array('deepsnow', 'freestyle'));
        $this->context['product'] = array('variants' => array(array('title' => 'draft151cm'), array('title' => 'element151cm')));

        $this->assertEquals(5, $this->context['products["count"]']);
        $this->assertEquals('deepsnow', $this->context['products["tags"][0]']);
        $this->assertEquals('deepsnow', $this->context['products["tags"].first']);
        $this->assertEquals('draft151cm', $this->context['product["variants"][0]["title"]']);
        $this->assertEquals('element151cm', $this->context['product["variants"][1]["title"]']);
        $this->assertEquals('draft151cm', $this->context['product["variants"][0]["title"]']);
        $this->assertEquals('element151cm', $this->context['product["variants"].last["title"]']);
    }

    public function test_access_variable_with_hash_notation() {
        $this->context['foo'] = 'baz';
        $this->context['bar'] = 'foo';

        $this->assertEquals('baz', $this->context['["foo"]']);
        $this->assertEquals('baz', $this->context['[bar]']);
    }

    public function test_access_hashes_with_hash_access_variables() {

        $this->context['var'] = 'tags';
        $this->context['nested'] = array('var' => 'tags');
        $this->context['products'] = array('count' => 5, 'tags' => array('deepsnow', 'freestyle'));

        $this->assertEquals('deepsnow', $this->context['products[var].first']);
        $this->assertEquals('freestyle', $this->context['products[nested.var].last']);
    }

    public function test_hash_notation_only_for_hash_access() {
        $this->context['array'] = array(1,2,3,4,5);
        $this->context['hash'] = array('first' => 'Hello');

        $this->assertEquals(1, $this->context['array.first']);
        $this->assertEquals(null, $this->context['array["first"]']);
        $this->assertEquals('Hello', $this->context['hash["first"]']);
    }

    public function test_first_can_appear_in_middle_of_callchain() {
        $this->context['product'] = array('variants' => array(array('title' => 'draft151cm'), array('title' => 'element151cm')));

        $this->assertEquals('draft151cm', $this->context['product.variants[0].title']);
        $this->assertEquals('element151cm', $this->context['product.variants[1].title']);
        $this->assertEquals('draft151cm', $this->context['product.variants.first.title']);
        $this->assertEquals('element151cm', $this->context['product.variants.last.title']);
    }

    public function test_cents() {
        $this->context->merge(array("cents" => new HundredCentes()));
        $this->assertEquals(100, $this->context['cents']);
    }

    public function test_nested_cents() {
        $this->context->merge(array("cents" => array('amount' => new HundredCentes())));
        $this->assertEquals(100, $this->context['cents.amount']);

        $this->context->merge(array("cents" => array('cents' => array('amount' => new HundredCentes()))));
        $this->assertEquals(100, $this->context['cents.cents.amount']);
    }

    public function test_cents_through_drop() {
        $this->context->merge(array("cents" => new CentsDrop()));
        $this->assertEquals(100, $this->context['cents.amount']);
    }

    public function test_nested_cents_through_drop() {
        $this->context->merge(array("vars" => array("cents" => new CentsDrop())));
        $this->assertEquals(100, $this->context['vars.cents.amount']);
    }

    public function test_drop_methods_with_question_marks() {
        $this->context->merge(array("cents" => new CentsDrop()));
        $this->assertTrue($this->context['cents.non_zero?']);
    }

    public function test_context_from_within_drop() {
        $this->context->merge(array("test" => '123', "vars" => new ContextSensitiveDrop()));
        $this->assertEquals('123', $this->context['vars.test']);
    }

    public function test_nested_context_from_within_drop() {
        $this->context->merge(array("test" => '123', "vars" => array("local" => new ContextSensitiveDrop())));
        $this->assertEquals('123', $this->context['vars.local.test']);
    }

    public function test_ranges() {
        $this->context->merge(array("test" => '5'));
        $this->assertEquals(range(1,5), $this->context['(1..5)']);
        $this->assertEquals(range(1,5), $this->context['(1..test)']);
        $this->assertEquals(range(5,5), $this->context['(test..test)']);
    }

    public function test_cents_through_drop_nestedly() {
        $this->context->merge(array("cents" => array("cents" => new CentsDrop())));
        $this->assertEquals(100, $this->context['cents.cents.amount']);

        $this->context->merge(array("cents" => array("cents" => array("cents" => new CentsDrop()))));
        $this->assertEquals(100, $this->context['cents.cents.cents.amount']);
    }

    public function test_drop_with_variable_called_only_once() {
        $this->context['counter'] = new CounterDrop();

        $this->assertEquals(1, $this->context['counter.count']);
        $this->assertEquals(2, $this->context['counter.count']);
        $this->assertEquals(3, $this->context['counter.count']);
    }

    public function test_drop_with_key_called_only_once() {
        $this->context['counter'] = new CounterDrop();

        $this->assertEquals(1, $this->context['counter["count"]']);
        $this->assertEquals(2, $this->context['counter["count"]']);
        $this->assertEquals(3, $this->context['counter["count"]']);
    }

    public function test_proc_as_variable() {
        $this->context['dynamic'] = new ProcAsVariable();

        $this->assertEquals('Hello', $this->context['dynamic']);
    }

    public function test_lambda_as_variable() {
        $this->context['dynamic'] = function() { return 'Hello'; };

        $this->assertEquals('Hello', $this->context['dynamic']);
    }

    public function test_nested_lambda_as_variable() {
        $this->context['dynamic'] = array("lambda" => function() { return 'Hello'; });

        $this->assertEquals('Hello', $this->context['dynamic.lambda']);
    }

    public function test_array_containing_lambda_as_variable() {
        $this->context['dynamic'] = array(1,2, function() { return 'Hello'; } ,4,5);

        $this->assertEquals('Hello', $this->context['dynamic[2]']);
    }
    public function test_lambda_is_called_once() {
        $global = 0;
        $this->context['callcount'] = function() use (&$global) { $global += 1; return (string) $global; };

        $this->assertEquals('1', $this->context['callcount']);
        $this->assertEquals('1', $this->context['callcount']);
        $this->assertEquals('1', $this->context['callcount']);
    }

    public function test_nested_lambda_is_called_once() {
        $global = 0;
        $this->context['callcount'] = array("lambda" => function() use (&$global) { $global += 1; return (string) $global; });

        $this->assertEquals('1', $this->context['callcount.lambda']);
        $this->assertEquals('1', $this->context['callcount.lambda']);
        $this->assertEquals('1', $this->context['callcount.lambda']);
    }

    public function test_lambda_in_array_is_called_once() {
        $global = 0;
        $this->context['callcount'] = array(
            1,2, function() use (&$global) { $global += 1; return (string) $global; } ,4,5
        );

        $this->assertEquals('1', $this->context['callcount[2]']);
        $this->assertEquals('1', $this->context['callcount[2]']);
        $this->assertEquals('1', $this->context['callcount[2]']);
    }

    public function test_access_to_context_from_proc() {
        $registers = $this->context->registers();
        $registers['magic'] = 345392;

        $this->context['magic'] = function($context) { $registers = $context->registers(); return $registers['magic']; };

        $this->assertEquals(345392, $this->context['magic']);
    }

    public function test_to_liquid_and_context_at_first_level() {
        $this->context['category'] = new Category("foobar");

        $this->assertInstanceOf('\Liquid\Tests\Lib\CategoryDrop',$this->context['category']);
        $this->assertEquals($this->context, $this->context['category']->context());
    }

    public function test_strict_variables_not_found() {
        $this->context['does_not_exists'];
        $this->assertEquals(1, count($this->context->errors()));
        $errors = $this->context->errors();
        $this->assertEquals('Variable {{does_not_exists}} not found', $errors[0]);
    }

    public function test_strict_nested_variables_not_found() {

        $this->context['hash'] = array('this' => 'exists');
        $this->context['hash.does_not_exist'];

        $this->assertEquals(1, count($this->context->errors()));
        $errors = $this->context->errors();
        $this->assertEquals('Variable {{hash.does_not_exist}} not found', $errors[0]);
    }

}

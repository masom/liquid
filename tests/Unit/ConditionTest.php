<?php


namespace Liquid\Tests\Unit;


use Liquid\Condition;
use Liquid\Context;
use Liquid\StandardFilters;
use Liquid\Strainer;
use Liquid\Tests\TestCase;


class ConditionTest extends TestCase
{
    /** @var Context */
    protected $context;

    protected function setUp()
    {
        parent::setUp();
        $this->context = new Context();
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($this->context);
    }


    public function test_basic_condition()
    {

        $condition = new Condition('1', '==', '2');
        $this->assertFalse($condition->evaluate());

        $condition = new Condition('1', '==', '1');
        $this->assertTrue($condition->evaluate());
    }

    public function test_default_operators_evalute_true()
    {
        $this->assert_evalutes_true('1', '==', '1');
        $this->assert_evalutes_true('1', '!=', '2');
        $this->assert_evalutes_true('1', '<>', '2');
        $this->assert_evalutes_true('1', '<', '2');
        $this->assert_evalutes_true('2', '>', '1');
        $this->assert_evalutes_true('1', '>=', '1');
        $this->assert_evalutes_true('2', '>=', '1');
        $this->assert_evalutes_true('1', '<=', '2');
        $this->assert_evalutes_true('1', '<=', '1');
        # negative numbers
        $this->assert_evalutes_true('1', '>', '-1');
        $this->assert_evalutes_true('-1', '<', '1');
        $this->assert_evalutes_true('1.0', '>', '-1.0');
        $this->assert_evalutes_true('-1.0', '<', '1.0');
    }

    public function test_default_operators_evalute_false()
    {
        $this->assert_evalutes_false('1', '==', '2');
        $this->assert_evalutes_false('1', '!=', '1');
        $this->assert_evalutes_false('1', '<>', '1');
        $this->assert_evalutes_false('1', '<', '0');
        $this->assert_evalutes_false('2', '>', '4');
        $this->assert_evalutes_false('1', '>=', '3');
        $this->assert_evalutes_false('2', '>=', '4');
        $this->assert_evalutes_false('1', '<=', '0');
        $this->assert_evalutes_false('1', '<=', '0');
    }

    public function test_contains_works_on_strings()
    {
        $this->assert_evalutes_true("'bob'", 'contains', "'o'");
        $this->assert_evalutes_true("'bob'", 'contains', "'b'");
        $this->assert_evalutes_true("'bob'", 'contains', "'bo'");
        $this->assert_evalutes_true("'bob'", 'contains', "'ob'");
        $this->assert_evalutes_true("'bob'", 'contains', "'bob'");

        $this->assert_evalutes_false("'bob'", 'contains', "'bob2'");
        $this->assert_evalutes_false("'bob'", 'contains', "'a'");
        $this->assert_evalutes_false("'bob'", 'contains', "'---'");
    }

    public function test_invalid_comparation_operator()
    {
        $this->assert_evaluates_argument_error("1", '~~', '0');
    }

    public function test_comparation_of_int_and_str()
    {
        $this->assert_evaluates_argument_error("'1'", '>', '0');
        $this->assert_evaluates_argument_error("'1'", '<', '0');
        $this->assert_evaluates_argument_error("'1'", '>=', '0');
        $this->assert_evaluates_argument_error("'1'", '<=', '0');
    }

    public function test_contains_works_on_arrays()
    {
        $this->context['array'] = array(1, 2, 3, 4, 5);

        $this->assert_evalutes_false("array", 'contains', '0');
        $this->assert_evalutes_true("array", 'contains', '1');
        $this->assert_evalutes_true("array", 'contains', '2');
        $this->assert_evalutes_true("array", 'contains', '3');
        $this->assert_evalutes_true("array", 'contains', '4');
        $this->assert_evalutes_true("array", 'contains', '5');
        $this->assert_evalutes_false("array", 'contains', '6');
        $this->assert_evalutes_false("array", 'contains', '"1"');
    }

    public function test_contains_returns_false_for_nil_operands()
    {
        $this->assert_evalutes_false("not_assigned", 'contains', '0');
        $this->assert_evalutes_false("0", 'contains', 'not_assigned');
    }

    public function test_or_condition()
    {
        $condition = new Condition('1', '==', '2');

        $this->assertFalse($condition->evaluate());

        $condition->orCondition(new Condition('2', '==', '1'));

        $this->assertFalse($condition->evaluate());

        $condition->orCondition(new Condition('1', '==', '1'));

        $this->assertTrue($condition->evaluate());
    }

    public function test_and_condition()
    {
        $condition = new Condition('1', '==', '1');

        $this->assertTrue($condition->evaluate());

        $condition->andCondition(new Condition('2', '==', '2'));

        $this->assertTrue($condition->evaluate());

        $condition->andCondition(new Condition('2', '==', '1'));

        $this->assertFalse($condition->evaluate());
    }

    public function test_should_allow_custom_proc_operator()
    {
        $operators = Condition::operators();
        $operators['starts_with'] = function ($cond, $left, $right) {
            return preg_match("/^{$right}/", $left);
        };

        $this->assert_evalutes_true("'bob'", 'starts_with', "'b'");
        $this->assert_evalutes_false("'bob'", 'starts_with', "'o'");

        $operators->offsetUnset('starts_with');
    }

    public function test_left_or_right_may_contain_operators()
    {
        $this->context['one'] = $this->context['another'] = "gnomeslab-and-or-liquid";

        $this->assert_evalutes_true("one", '==', "another");
    }

    protected function assert_evalutes_true($left, $op, $right)
    {
        $condition = new Condition($left, $op, $right);
        $this->assertEquals(
            $condition->evaluate($this->context),
            "Evaluated false: #{left} #{op} #{right}"
        );
    }

    protected function assert_evalutes_false($left, $op, $right)
    {
        $condition = new Condition($left, $op, $right);
        $this->assertEquals(
            !$condition->evaluate($this->context),
            "Evaluated true: #{left} #{op} #{right}"
        );
    }

    protected function assert_evaluates_argument_error($left, $op, $right)
    {
        try {
            $condition = new Condition($left, $op, $right);
            $condition->evaluate($this->context);
            $this->fail('An InvalidArgumentException should have been thrown.');
        } catch (\Liquid\Exceptions\ArgumentError $e) {

        }
    }
}

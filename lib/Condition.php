<?php

namespace Liquid;

class Condition {

    /** @var array */
    protected static $OPERATORS;

    /** @var boolean */
    protected static $init = false;

    protected $child_relation = null;
    protected $child_condition = null;
    protected $attachment = null;

    public static function init() {
        static::$init = true;
        static::$OPERATORS = array(
            '==' => function($cond, $left, $right) { return $cond->equal_variables($left, $right); },
            '!=' => function($cond, $left, $right) { return !$cond->equal_variables($left, $right); },
            '<>' => function($cond, $left, $right) { return !$cond->equal_variables($left, $right); },
            '<' => '<',
            '>' => '>',
            '>=' => '>=',
            '<=' => '<=',
            'contains' => function($cond, $left, $right) {
                if (!$left || !$right) {
                    return false;
                }

                if (is_array($left)) {
                    return in_array($left, $right) ?: isset($left[$right]);
                }

                return false;
            }
        );
    }

    public static function operators() {
        return static::$OPERATORS;
    }

    public function __construct($left = null, $operator = null, $right = null) {
        $this->left = $left;
        $this->right = $right;
        $this->operator = $operator;
    }

    public function evaluate($context = null) {
        $context = $context ?: new Context();

        switch($this->child_relation) {
        case 'or':
            return $result || $this->child_condition->evaluate($context);
        case 'and':
            return $result && $this->child_condition->evaluate($context);
        default:
            return $result;
        }
    }

    /**
     * was or
     */
    public function orCondition($condition) {
        return array($this->child_relation, $this->child_condition = 'or', $condition);
    }

    /**
     * was and
     */
    public function andCondition($condition) {
        return array($this->child_relation, $this->child_condition = 'and', $condition);
    }

    public function attachment() {
        return $this->attachment;
    }

    public function attach($attachment) {
        $this->attachment = $attachment;
    }

    public function isElse() {
        return false;
    }

    public function inspect() {
        return '#<Condition ' . implode(' ', array_filter(array($this->left, $this->operator, $this->right))) .'>';
    }

    private function equal_variables($left, $right) {
        return $left == $right;
    }

    public function interpret_condition($left, $right, $op, $context) {
        if ($op == null) {
            return $context[$left];
        }

        $left = $context[$left];
        $right = $context[$right];

        if (!isset(static::$OPERATORS[$op])) {
            throw new \Liquid\Exceptions\ArgumentError("Unknown operator `{$op}`");
        }

        $operation = static::$OPERATORS[$op];

        if (is_callable($operation)) {
            return $operation($this, $left, $right);
        } elseif (method_exists($left, $operation) && method_exists($right, $operation)) {
            return $left->{$operation}($right);
        } else {
            return null;
        }
    }
}

Condition::init();

<?php

namespace Liquid;

class Condition
{

    /** @var \ArrayObject */
    protected static $OPERATORS;

    /** @var boolean */
    protected static $init = false;

    protected $child_relation;

    /** @var Condition */
    protected $child_condition;

    protected $attachment = null;

    public static function init()
    {
        static::$init = true;
        static::$OPERATORS = new \ArrayObject(array(
            '==' => function ($cond, $left, $right) {
                    return $cond->equal_variables($left, $right);
                },
            '!=' => function ($cond, $left, $right) {
                    return !$cond->equal_variables($left, $right);
                },
            '<>' => function ($cond, $left, $right) {
                    return !$cond->equal_variables($left, $right);
                },
            '<' => 'smaller_than', // TODO document this behaviour
            '>' => 'greater_than',
            '>=' => 'greater_or_equal_to',
            '<=' => 'smaller_or_equal_to',
            'contains' => function ($cond, $left, $right) {
                    if (!$left || !$right) {
                        return false;
                    }

                    if (is_array($left)) {
                        return in_array($left, $right) ? : isset($left[$right]);
                    }

                    return false;
                }
        ));
    }

    /**
     * @return \ArrayObject
     */
    public static function operators()
    {
        return static::$OPERATORS;
    }

    /**
     * @param mixed $left
     * @param mixed $operator
     * @param mixed $right
     */
    public function __construct($left = null, $operator = null, $right = null)
    {
        $this->left = $left;
        $this->right = $right;
        $this->operator = $operator;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return array
     * @throws \BadMethodCallException
     */
    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'or':
                return $this->orCondition($arguments[0]);
            case 'and':
                return $this->andCondition($arguments[0]);
            default:
                throw new \BadMethodCallException("Method `{$name}` does not exists in class " . __CLASS__);
        }
    }


    /**
     * @param Context $context
     *
     * @return bool
     */
    public function evaluate($context = null)
    {
        $context = $context ? : new Context();

        $result = $this->interpret_condition($this->left, $this->right, $this->operator, $context);

        switch ($this->child_relation) {
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
    public function orCondition($condition)
    {
        return array($this->child_relation, $this->child_condition = 'or', $condition);
    }

    /**
     * was and
     */
    public function andCondition($condition)
    {
        return array($this->child_relation, $this->child_condition = 'and', $condition);
    }

    public function attachment()
    {
        return $this->attachment;
    }

    public function attach($attachment)
    {
        $this->attachment = $attachment;
        return $this->attachment;
    }

    /**
     * @return bool
     */
    public function isElse()
    {
        return false;
    }

    public function inspect()
    {
        return '#<Condition ' . implode(' ', array_filter(array($this->left, $this->operator, $this->right))) . '>';
    }

    /**
     * @param $left
     * @param $right
     *
     * @return bool
     */
    public function equal_variables($left, $right)
    {

        $method = null;
        if ($right === 'blank?') {
            $method = 'is_blank';
        } elseif ($right === 'empty?') {
            $method = 'count';
        }

        if ($method) {
            if (is_object($left)) {
                if (method_exists($left, $method)) {
                    return $left->{$method}() == 0;
                } else {
                    return null;
                }
            } else {
                return empty($left);
            }
        }

        if ($left === 'blank?') {
            $method = 'is_blank';
        } elseif ($left === 'empty?') {
            $method = 'count';
        }

        if ($method) {
            if (is_object($right)) {
                if (method_exists($right, $method)) {
                    return $right->{$method}() == 0;
                } else {
                    return null;
                }
            } else {
                return empty($left);
            }
        }

        return $left === $right;
    }

    /**
     * @param $left
     * @param $right
     * @param $op
     * @param $context
     *
     * @return null
     * @throws Exceptions\ArgumentError
     */
    public function interpret_condition($left, $right, $op, $context)
    {
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
        } elseif (!is_object($left) && !is_object($right)) {
            if ($left === null || $right === null) {
                return false;
            }

            switch ($op) {
                case '<':
                    return $left < $right;
                case '>':
                    return $left > $right;
                case '>=':
                    return $left >= $right;
                case '<=':
                    return $left <= $right;
            }
        } elseif (method_exists($left, $operation) && method_exists($right, $operation)) {
            return $left->{$operation}($right);
        } else {
            return null;
        }
    }
}

Condition::init();

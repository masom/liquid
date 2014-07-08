<?php

namespace Liquid;

use \Liquid\Strainer;
use \Liquid\Utils\Arrays;
use \Liquid\Utils\ArrayObject;
use \Liquid\Utils\Environments;
use \Liquid\Utils\Registers;
use \Liquid\Utils\Scopes;


class Context implements \ArrayAccess {

    /** @var Environments */
    protected $environments;

    /** @var Scopes */
    protected $scopes;

    /** @var Registers */
    protected $registers;

    /** @var array */
    protected $errors = array();

    /** @var boolean */
    protected $rethrow_errors;

    /** @var ArrayObject */
    protected $resource_limits;

    /** @var array */
    protected $interrupts = array();

    /** @var array */
    protected $filters = array();

    /** @var \Liquid\Strainer */
    protected $strainer;

    /** @var \ReflectionMethod */
    protected $strainerMethodInvoker;

    private static $LITERALS = array(
        null => null, 'nil' => null, 'null' => null, '' => null,
        'true' => true,
        'false' => false,
        'blank' => 'blank?',
        'empty' => 'empty?'
    );

    public function __construct(array $environments = array(), $outer_scope = array(), $registers = array(), $rethrow_errors = false, $resource_limits = array()) {
        $this->environments = new Environments(Arrays::flatten($environments));

        $this->scopes = new Scopes(array($outer_scope));

        $this->registers =  ($registers instanceof Registers) ? $registers : new Registers($registers);

        $this->rethrow_errors = $rethrow_errors;

        $resource_limits_defaults = array('render_score_current' => 0, 'assign_score_current' => 0);
        if (is_array($resource_limits)) {
            $this->resource_limits =  new ArrayObject($resource_limits + $resource_limits_defaults);
        } elseif($resource_limits instanceof ArrayObject) {
            $this->resource_limits = $resource_limits->merge($resource_limits_defaults);
        } else {
            $this->resource_limits = new ArrayObject($resource_limits_defaults);
        }

        $this->squash_instance_assigns_with_environments();

        /**
         * Faster than call_user_func_array
         */
        $this->strainerMethodInvoker = new \ReflectionMethod('\Liquid\Strainer', 'invoke');
    }

    /**
     * @return Environments
     */
    public function environments() {
        return $this->environments;
    }

    /**
     * @return ArrayObject
     */
    public function resource_limits() {
        return $this->resource_limits;
    }

    /**
     * @return \Liquid\Utils\Registers
     */
    public function registers() {
        return $this->registers;
    }

    /**
     *  context.scopes.last[@to] = val
     */
    public function scopes_last_set($to, $val) {
        $last = $this->scopes->last();
        $last[$to] = $val;
    }

    public function increment_used_resources($key, $obj) {

        if (is_array($obj)) {
            $increment = count($obj);
        } elseif(is_string($obj)) {
            $increment = mb_strlen($obj);
        } else {
            $increment = 1;
        }

        if (!isset($this->resource_limits[$key])) {
            $this->resource_limits[$key] = 0;
        }

        $this->resource_limits[$key] += $increment;
    }

    public function is_resource_limits_reached() {
        $limits = array(
            'render_length',
            'render_score',
            'assign_score'
        );

        foreach($limits as $name){
            if (isset($this->resource_limits[$name . '_limit']) && isset($this->resource_limits[$name . '_current'])) {
                if ($this->resource_limits[$name . '_current'] > $this->resource_limits[$name . '_limit']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return \Liquid\Strainer
     */
    public function strainer() {
        if (!$this->strainer) {
            $this->strainer = Strainer::create($this, $this->filters);
        }

        return $this->strainer;
    }

    public function add_filters($filters) {
        $filters = array_filter(Arrays::flatten(array($filters)));

        foreach($filters as $f) {
            if (!is_object($f)) {
                $class = gettype($f);
                throw new \InvalidArgumentException("Expected object but got: `{$class}`");
            }

            Strainer::add_known_filter($f);
        }

        if ($this->strainer) {
            foreach($filters as $f) {
                $this->strainer->extend($f);
            }
        } else {
            $this->filters = array_merge($this->filters, $filters);
        }
    }

    public function has_interrupt() {
        return !empty($this->interrupts);
    }

    public function push_interrupt($e) {
        $this->interrupts[] = $e;
    }

    public function pop_interrupt() {
        return array_pop($this->interrupts);
    }

    public function handle_error(\Exception $e) {
        $this->errors[] = $e;

        if ($this->rethrow_errors) {
            throw $e;
        }

        switch(true){
        case $e instanceof \Liquid\Exceptions\SyntaxError:
            return "Liquid syntax error: " . $e->getMessage();
        default:
            return "Liquid error: " . $e->getMessage();
        }
    }

    public function errors() {
        return $this->errors;
    }

    public function invoke($method) {
        $args = func_get_args();
        return $this->strainerMethodInvoker->invokeArgs( $this->strainer(), $args);
    }

    public function push(array $new_scope = array()) {
        $this->scopes->push($new_scope);

        if (count($this->scopes) > 100) {
            throw new \Liquid\Exceptions\StackLevelError("Nesting too deep.");
        }
    }

    public function merge(array $new_scopes) {
        $this->scopes->merge($new_scopes);
    }

    public function pop() {
        if (count($this->scopes) == 1) {
            throw new \Liquid\Exceptions\ContextError();
        }
        return $this->scopes->pop();
    }

    public function stack(\Closure $block, array $new_scope = array()) {
        $this->push($new_scope);

        try{
            $block($this);
        } catch(\Exception $e) {
            $this->pop();
            throw $e;
        }

        $this->pop();
    }

    public function clear_instance_assigns() {
        $this->scopes[0] = array();
    }

    /**
     * ArrayAccess
     */
    public function offsetExists($offset) {
        return $this->resolve($offset) ? true : false;
    }

    /**
     * ArrayAccess
     */
    public function offsetGet($offset) {
        return $this->resolve($offset);
    }

    /**
     * ArrayAccess
     */
    public function offsetSet($offset, $value) {
        $this->scopes[0][$offset] = $value;
    }

    /**
     * ArrayAccess
     */
    public function offsetUnset($offset) {
        unset($this->scopes[0][$offset]);
    }

    /**
     * @param string $key
     *
     * @return array|float|int|mixed
     */
    public function resolve($key) {
        if (isset(static::$LITERALS[$key])) {
            return static::$LITERALS[$key];
        }

        $matches = null;
        switch(true) {
        case preg_match('/\A\'(.*)\'\z/s', $key, $matches): //Single quoted strings
            return $matches[1];
        case preg_match('/\A"(.*)"\z/s', $key, $matches): // Double quoted
            return $matches[1];
        case preg_match('/\A(-?\d+)\z/', $key, $matches): // Integer
            return (int) $matches[1];
        case preg_match('/\A\((\S+)\.\.(\S+)\)\z/', $key, $matches): //Ranges
            return range((int) $this->resolve($matches[1]), (int) $this->resolve($matches[2]));
        case preg_match('/\A(-?\d[\d\.]+)\z/', $key, $matches): //Floats
            return (float) $matches[1];
        default:
            return $this->variable($key);
        }
    }

    /**
     * @param string $key
     *
     * @return Variable
     */
    public function find_variable($key) {
        $scope = null;
        $variable = null;

        foreach($this->scopes as $s) {
            if (!isset($s[$key])){
                continue;
            }

            $scope = $s;
            break;
        }

        if ($scope == null) {
            foreach($this->environments as $e) {
                $variable = $this->lookup_and_evaluate($e, $key);

                if ($variable !== null) {
                    $scope =& $e;
                    break;
                }
            }
        }

        if (!$scope) {
            if ($this->environments) {
                $scope = $this->environments->last();
            } else {
                $scope = $this->scopes->last();
            }
        }

        if (!isset($scope[$key])) {
            $this->handle_not_found($key);
        }

        $variable = $variable ?: $this->lookup_and_evaluate($scope, $key);

        if (is_object($variable)) {
            if (method_exists($variable, 'to_liquid')){
                $variable = $variable->to_liquid();
            }
            //TODO Should an exception be raised / warning logged? This is potentially unsafe.
        }
        if (method_exists($variable, 'context')) {
            $variable->context($this);
        }

        return $variable;
    }

    /**
     * @param string $markup
     *
     * @return mixed
     */
    public function variable($markup) {
        $parts = null;
        preg_match_all(\Liquid\Liquid::$VariableParser, $markup, $parts);

        $square_braketed = '/\A\[(.*)\]\z/s';

        $first_part = array_shift($parts[0]);

        $matches = null;
        if (preg_match($square_braketed, $first_part, $matches)) {
            $first_part = $this->resolve($matches[1]);
        }

        if ($object = $this->find_variable($first_part)) {
            foreach($parts[0] as $part) {
                $matches = null;
                $part_resolved = preg_match($square_braketed, $part, $matches);

                if ($part_resolved) {
                    $part = $this->resolve($matches[1]);
                }

                if ((is_array($object) || $object instanceof \ArrayAccess) && isset($object[$part])) {
                    $res = $this->lookup_and_evaluate($object, $part);

                    if (is_object($res) && method_exists($res, 'to_liquid')) {
                        $object = $res->to_liquid();
                    } else {
                        $object = $res;
                    }
                } elseif (!$part_resolved && in_array($part, array('size', 'first', 'last'))) {
                    if (method_exists($object, $part)) {
                        $res = $object->{$part}();
                    } else {
                        switch($part) {
                        case 'size':
                            $res = count($object);
                            break;
                        case 'first':
                            $res = reset($object);
                            break;
                        case 'last':
                            $res = end($object);
                            break;
                        }
                    }
                    if (is_object($res) && method_exists($res, 'to_liquid')) {
                        $object = $res->to_liquid();
                    } else {
                        //TODO Maybe throw an exception if the object does not support to_liquid?
                        $object = $res;
                    }
                } else {
                    $this->handle_not_found($markup);
                    return null;
                }

                if (method_exists($object, 'context')) {
                    $object->context($this);
                }
            }
        }

        return $object;
    }

    /**
     * @param mixed $obj
     * @param string $key
     *
     * @return mixed
     */
    public function lookup_and_evaluate(&$obj, $key) {

        if (!isset($obj[$key])) {
            return null;
        }

        $value = $obj[$key];

        if (($value instanceof \Closure || is_callable($value))
            && (is_array($obj) || $obj instanceof \ArrayAccess)) {
            /**
             * PHP doesn't really care if we pass more arguments.
             */
            $obj[$key] = $value($this);
            return $obj[$key];
        } else {
            return $value;
        }
    }

    public function squash_instance_assigns_with_environments() {
        $scope = $this->scopes->last();
        foreach($scope as $k => $v) {
            foreach($this->environments as $env) {
                if (isset($env[$k])) {
                    $scope[$k] = $this->lookup_and_evaluate($env, $k);
                    break;
                }
            }
        }
    }

    public function handle_not_found($variable) {
        $this->errors[] = "Variable {{{$variable}}} not found";
    }
}

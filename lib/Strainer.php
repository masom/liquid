<?php

namespace Liquid;

class Strainer {

    /** @var array */
    protected static $filters;
    /** @var array */
    protected static $global_filters;
    /** @var array */
    protected static $global_methods;

    /**
     * @var array
     */
    protected $instance_filters = array();

    /**
     * @var array
     */
    protected $instance_methods = array();

    /** @var \Liquid\Context $context */
    protected $context;

    /**
     * Re-initialize the Strainer.
     */
    public static function init() {
        static::$filters = array();
        static::$global_filters = array();
        static::$global_methods = array();
    }

    /**
     * @param Context $context
     */
    public function __construct(&$context) {
        $this->context = $context;
    }

    /**
     * @param object $filter
     */
    public static function global_filter($filter) {
        static::add_known_filter($filter);

        static::$filters[] = $filter;
    }

    /**
     * @param object $filter
     */
    public static function add_known_filter($filter) {
        static::add_filter(static::$global_filters, static::$global_methods, $filter);
    }

    /**
     * @param Context $context
     * @param array $filters
     *
     * @return Strainer
     */
    public static function create(&$context = null, array $filters = array())
    {
        $filters = array_merge(static::$filters, $filters);

        /** @var Strainer $instance */
        $instance = new static($context);

        foreach($filters as $filter) {
            $instance->add_filter($instance->instance_filters, $instance->instance_methods, $filter);
        }

        return $instance;
    }

    /**
     * Mimicks the Hash.extend method.
     */
    public function extend($filter) {
        static::add_filter($this->instance_filters, $this->instance_methods, $filter);
    }

    /**
     *  hash[filters] = Class.new(Strainer) do
     *   filters.each { |f| include f }
     *  end
     */
    protected static function add_filter(&$known_filters, &$known_methods, $filter) {
        $class = get_class($filter);

        if (isset($known_filters[$class])) {
            // Filter already loaded.
            return;
        }

        $methods = get_class_methods($filter);

        foreach($methods as $method) {
            if ($method === '__construct') {
                continue;
            }

            $known_methods[$method] = $class;
        }

        $known_filters[$class] = $filter;
    }

    /**
     * @param string $method
     * @return mixed
     */
    public function invoke($method) {

        $args = func_get_args();

        array_shift($args);

        if ($this->is_invokable($method)) {

            $class = $this->instance_methods[$method];
            $instance = $this->instance_filters[$class];

            if (count($args)) {
                $arg_count = 1;
                if (isset($args[1])) {
                    $arg_count += count($args[1]);
                }
            } else {
                $arg_count = 0;
            }

            /**
            * Optimize calling the method with less than 5 arguments
             */
            switch($arg_count) {
                case 0:
                    return $instance->{$method}();
                case 1:
                    return $instance->{$method}($args[0]);
                case 2:
                    return $instance->{$method}($args[0], $args[1][0]);
                case 3:
                    return $instance->{$method}($args[0], $args[1][0], $args[1][1]);
                case 4:
                    return $instance->{$method}($args[0], $args[1][0], $args[1][1], $args[1][2]);
                case 5:
                    return $instance->{$method}($args[0], $args[1][0], $args[1][1], $args[1][2], $args[1][3]);
                default:
                    return call_user_func_array( array( $instance, $method ), $args[1] );
                break;
            }
        } else {
            return array_shift($args);
        }
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    public function is_invokable($method) {
        if (!isset($this->instance_methods[$method])) {
            return false;
        }

        $class = $this->instance_methods[$method];
        $instance = $this->instance_filters[$class];
        return is_callable( array( $instance, $method ) );
    }
}

<?php

namespace Liquid;

class Strainer {

    protected static $filters = array();
    protected static $global_filters = array();
    protected static $global_methods = array();

    protected $instance_filters = array();
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

    public function __construct( $context ) {
        $this->context = $context;
    }

    public static function global_filter($filter) {
        static::add_known_filter($filter);

        static::$filters[] = $filter;
    }

    public static function add_known_filter($filter) {
        static::add_filter(static::$global_filters, static::$global_methods, $filter);
    }

    public static function strainer_class_cache() {
        return static::$strainer_class_cache;
    }

    public static function create($context, array $filters = array())
    {
        $filters = array_merge(static::$filters, $filters);
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

    public function invoke($method) {

        $args = func_get_args();

        array_shift($args);

        if ($this->is_invokable($method)) {

            $class = $this->instance_methods[$method];
            $instance = $this->instance_filters[$class];

            /**
            * Optimize calling the method with less than 5 arguments
             */
            switch(count($args)) {
                case 0:
                    return $instance->{$method}();
                case 1:
                    return $instance->{$method}($args[0]);
                case 2:
                    return $instance->{$method}($args[0], $args[1]);
                case 3:
                    return $instance->{$method}($args[0], $args[1], $args[2]);
                case 4:
                    return $instance->{$method}($args[0], $args[1], $args[2], $args[3]);
                case 5:
                    return $instance->{$method}($args[0], $args[1], $args[2], $args[3], $args[4]);
                default:
                    return call_user_func_array( array( $instance, $method ), $args );
                break;
            }
        } else {
            return array_shift($args);
        }
    }

    public function is_invokable($method) {
        if (!isset($this->instance_methods[$method])) {
            return false;
        }

        $class = $this->instance_methods[$method];
        $instance = $this->instance_filters[$class];
        return is_callable( array( $instance, $method ) );
    }
}

<?php

namespace \Liquid;

class Strainer {

    protected static $filters = array();
    protected static $known_filters = array();
    protected static $known_methods = array();
    protected static $strainer_class_cache = array();

    protected $context;

    public function __construct( $context ) {
        $this->context = $context;
    }

    public static function global_filter($filter) {
        static::add_known_filter($filter);

        static::$filters[] = $filter;
    }

    public static function add_known_filter($filter) {

        $class = get_class($filter);

        if (isset(static::$known_filters[$class])) {
            // Filter already loaded.
            return;
        }

        $methods = get_class_methods($filter);

        foreach($methods as $method) {
            if ($method === '__construct') {
                continue;
            }

            if (isset(static::$known_methods[$method])) {
                continue;
            }

            static::$known_methods[$method] = $class;
        }

        static::$known_filters[$class] = $filter;
    }

    public static function strainer_class_cache() {
        return static::$strainer_class_cache;
    }

    public static function create($context, array $filters = array())
    {
        //TODO Not sure why this is being created like that.
        $filters = static::$filters + $filters;
        $instance = new static($context);
        
        static::$strainer_class_cache[$filters] = $instance;

        return $instance;
    }

    public function invoke($method, array $args = array() ) {
        if ($this->invokable($method)) {
            $class = static::$known_methods[$method];
            $instance = static::$known_filters[$class];

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
            // Interesting behaviour.
            return array_pop($args);
        }
    }

    public function invokable($method) {

        if (!isset(static::$known_methods[$method])) {
            return false;
        }

        $class = static::$known_methods[$method];
        $instance = static::$known_filters[$class];
        return is_callable( array( $instance, $method ) );
    }
}

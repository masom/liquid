<?php

namespace Liquid;

use \Liquid\Strainer;
use \Liquid\Utils\Registers;

class Template {

    protected static $error_mode;

    protected static $filesystem;
    
    /** @var array */
    protected static $tags;

    /** @var array */
    protected $resource_limits = array();

    protected $warnings;

    protected $root;

    /** @var Registers */
    protected $registers;

    /** @var \ArrayObject */
    protected $assigns;

    /** @var \ArrayObject */
    protected $instance_assigns;

    /** @var array */
    protected $errors = array();

    /** @var boolean */
    protected $rethrow_errors = true;

    public function __construct() {
        $this->registers = new Registers();
        $this->assigns = new \ArrayObject();
        $this->instance_assigns = new \ArrayObject();
    }

    /**
     * Get or set the filesystem.
     *
     * @param object $obj Set the filesystem to the provided $obj.
     * @return object|null
     */
    public static function filesystem( $obj = null) {
        if (!$obj) {
            return $this->filesystem;
        }

        static::$filesystem = $obj;
    }

    /**
     * Register a tag.
     *
     * @param string $name
     * @param string $class
     */
    public static function register_tag($name, $class) {
        static::$tags[$name] = $class;
    }

    /**
     * Get the configured tags.
     *
     * @return array
     */
    public static function tags() {
        return static::$tags;
    }

    public static function register_filter( $filter ) {
        Strainer::global_filter($filter);
    }

    public static function error_mode($error_mode = null) {
        if ($error_mode) {
            static::$error_mode = $error_mode;
            return;
        }
        return static::$error_mode ?: Liquid::ERROR_MODE_LAX;
    }

    public static function __callStatic($method, $args) {
        if ($method === 'parse') {
            $template = new static();

            $tokens =& $args[0];
            $options = isset($args[1]) ? $args[1] : array();
            return $template->parse($args[0], $options);
        }

        throw new \BadMethodCallException("Method `" . __CLASS__ . "::{$method}` is undefined.");
    }

    public function __call($method, $args) {
        if ($method === 'parse') {
            $source = $args[0];
            $options = isset($args[1]) ? $args[1] : array();
            $this->root = Document::parse($this->tokenize($source), $options);
            $this->warnings = null;

            return $this;
        }

        throw new \BadMethodCallException("Method `" . __CLASS__ ."->{$method}` is undefined.");
    }

    public function root() {
        return $this->root;
    }

    public function warnings() {
        if(!$this->root) {
            return array();
        }

        return $this->warnings ?: $this->root->warnings();
    }

    public function registers() {
        return $this->registers;
    }

    public function instance_assigns() {
        return $this->instance_assigns;
    }

    public function assigns() {
        return $this->assigns;
    }

    public function render() {
        if (!$this->root) {
            return '';
        }

        $args = func_get_args();

        $context = reset($args);

        switch(true) {
        case $context instanceof Context:
            $context = array_shift($args);
            $context->rethrow_errors($this->rethrow_errors);
            break;
        case $context instanceof Drop:
            $context = array_shift($args);
            $context->context(new Context(array($context, $this->assigns), $this->instance_assigns, $this->registers, $this->rethrow_errors, $this->resource_limits));
            break;
        case is_array($context):
            $context = new Context(array(new \ArrayObject(array_shift($args)), $this->assigns), $this->instance_assigns, $this->registers, $this->rethrow_errors, $this->resource_limits);
            break;
        case $context instanceof \ArrayObject:
            $context = new Context(array(array_shift($args), $this->assigns), $this->instance_assigns, $this->registers, $this->rethrow_errors, $this->resource_limits);
            break;
        case $context == null:
            $context = new Context(array($this->assigns), $this->instance_assigns, $this->registers, $this->rethrow_errors, $this->resource_limits);
            break;
        default:
            throw new \InvalidArgumentException("Expected array or \Liquid\Context as parameter");
        }


        $last = array_pop($args);
        switch(true){
        case is_array($last):
            // Merged when Hash and when Array clause
            //
            if (!isset($last['registers']) && !isset($last['filters'])) {
                $context->add_filters($last);
                break;
            }

            if (isset($last['registers']) && is_array($last['registers'])) {
                static::$registers = new \ArrayObject(array_merge(static::registers, $last['registers']));
            }

            if (isset($last['filters'])) {
                $context->add_filters($last['filters']);
            }
            break;
        case is_object($last):
            $context->add_filters($last);
            break;
        }

        try {
            $result = $this->root->render($context);

            $this->errors = $context->errors();

            return is_array($result) ? implode('\n', $result) : $result;
        } catch(\Liquid\MemoryError $e) {
            $context->handle_error($e);
            $this->errors = $context->errors();
        }
    }

    private function tokenize($source) {
        $source = method_exists($source, 'source') ? $source->source() : $source;

        if (empty($source)) {
            return array();
        }

        $tokens = preg_split(\Liquid\Liquid::$TemplateParser, $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        if (isset($tokens[0]) && empty($tokens[0])) {
            array_shift($tokens);
        }

        return new \Liquid\Utils\Tokens($tokens);
    }
}

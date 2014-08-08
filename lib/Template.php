<?php

namespace Liquid;

use \Liquid\Liquid;
use \Liquid\Strainer;
use \Liquid\Utils\ArrayObject;
use \Liquid\Utils\Registers;
use \Liquid\Utils\Tokens;

class Template
{

    protected static $error_mode;

    protected static $filesystem;

    /** @var array */
    protected static $tags;

    /** @var \ArrayObject */
    protected $resource_limits;

    protected $warnings;

    /** @var Document */
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
    protected $rethrow_errors = false;

    public function __construct()
    {
        $this->registers = new Registers();
        $this->assigns = new \ArrayObject();
        $this->instance_assigns = new \ArrayObject();
        $this->resource_limits = new ArrayObject();
    }

    public function rethrow_errors($rethrow = null)
    {
        if ($rethrow !== null) {
            $this->rethrow_errors = (bool)$rethrow;
        }

        return $this->rethrow_errors;
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * @return \ArrayObject|ArrayObject
     */
    public function resource_limits()
    {
        return $this->resource_limits;
    }

    /**
     * Get or set the filesystem.
     *
     * @param object $obj Set the filesystem to the provided $obj.
     * @return object|null
     */
    public static function filesystem($obj = null)
    {
        if (!$obj) {
            return static::$filesystem;
        }

        static::$filesystem = $obj;
    }

    /**
     * Register a tag.
     *
     * @param string $name
     * @param string $class
     */
    public static function register_tag($name, $class)
    {
        static::$tags[$name] = $class;
    }

    /**
     * Get the configured tags.
     *
     * @return array
     */
    public static function tags()
    {
        return static::$tags;
    }

    /**
     * @param $filter
     */
    public static function register_filter($filter)
    {
        Strainer::global_filter($filter);
    }

    /**
     * @param string $error_mode
     *
     * @return string
     */
    public static function error_mode($error_mode = null)
    {
        if ($error_mode) {
            static::$error_mode = $error_mode;
            return static::$error_mode;
        }

        return static::$error_mode ? : Liquid::ERROR_MODE_LAX;
    }

    /**
     * @param $method
     * @param $args
     *
     * @return mixed
     * @throws \BadMethodCallException
     */
    public static function __callStatic($method, $args)
    {
        if ($method === 'parse') {
            $template = new static();
            /** @var Template $template */

            $options = isset($args[1]) ? $args[1] : array();
            return $template->parse($args[0], $options);
        }

        throw new \BadMethodCallException("Method `" . __CLASS__ . "::{$method}` is undefined.");
    }

    /**
     * @param string $method
     * @param array $args
     *
     * @return $this
     * @throws \BadMethodCallException
     */
    public function __call($method, $args)
    {
        if ($method === 'parse') {
            $source = $args[0];
            $options = isset($args[1]) ? $args[1] : array();

            $this->root = Document::parse($this->tokenize($source), $options);
            $this->warnings = null;

            return $this;
        }

        throw new \BadMethodCallException("Method `" . __CLASS__ . "->{$method}` is undefined.");
    }

    public function root()
    {
        return $this->root;
    }

    /**
     * @return array
     */
    public function warnings()
    {
        if (!$this->root) {
            return array();
        }

        return $this->warnings ? : $this->root->warnings();
    }

    /**
     * @return Registers
     */
    public function registers()
    {
        return $this->registers;
    }

    /**
     * @return \ArrayObject
     */
    public function instance_assigns()
    {
        return $this->instance_assigns;
    }

    /**
     * @return \ArrayObject
     */
    public function assigns()
    {
        return $this->assigns;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function render()
    {
        if (!$this->root) {
            return '';
        }

        $args = func_get_args();

        $context = reset($args);

        switch (true) {
            case $context instanceof Context:
                $context = array_shift($args);
                if ($this->rethrow_errors) {
                    $context->exception_handler(function ($e) {
                        return true;
                    });
                }
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
                throw new \InvalidArgumentException('Expected array or \Liquid\Context as parameter');
        }


        $last = array_pop($args);
        switch (true) {
            case is_array($last):
                // Merged when Hash and when Array clause
                //
                if (!isset($last['registers']) && !isset($last['filters'])) {
                    $context->add_filters($last);
                    break;
                }

                if (isset($last['registers']) && is_array($last['registers'])) {
                    $this->registers->merge($last['registers']);
                }

                if (isset($last['filters'])) {
                    $context->add_filters($last['filters']);
                }

                if (isset($last['exception_handler'])) {
                    $context->exception_handler($last['exception_handler']);
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
        } catch (\Liquid\Exceptions\MemoryError $e) {
            $this->errors = $context->errors();
            return $context->handle_error($e);
        }
    }

    /**
     * @param mixed $source
     *
     * @return Utils\Tokens
     */
    private function tokenize($source)
    {
        $source = method_exists($source, 'source') ? $source->source() : $source;

        if (empty($source)) {
            return new Tokens();
        }

        $tokens = preg_split(Liquid::$TemplateParser, $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        if (isset($tokens[0]) && $tokens[0] === '') {
            array_shift($tokens);
        }

        return new Tokens($tokens);
    }
}

<?php

namespace Liquid;

class Drop implements \ArrayAccess {
    const EMPTY_STRING = '';

    /** @var Context */
    protected $context;

    /**
     * Allows mapping PHP reserved words to methods (ex: `non_zero?` => 'is_non_zero', `blank?` => 'is_blank' )
     * @var array
     */
    protected $invokable_methods_map = array();

    /**
     * @var array
     */
    protected $invokable_methods;

    /**
     * @param string $method
     *
     * @return null
     */
    public function before_method($method) {
        return null;
    }

    public function resource_limits() {
        return $this->context->resource_limits();
    }

    public function has_interrupt() {
        return false;
    }

    public function increment_used_resources() {

    }

    public function is_resource_limits_reached() {
        return false;
    }

    public function errors() {
        return array();
    }

    /**
     * @param string $method_or_key
     *
     * @return mixed
     */
    public function invoke_drop($method_or_key) {

        /**
         * Allow method names like `blank?`
         */
        if (isset($this->invokable_methods_map[$method_or_key])) {
            $method = $this->invokable_methods_map[$method_or_key];
            return $this->{$method}();
        }

        if ($method_or_key && $method_or_key != static::EMPTY_STRING && $this->is_invokable($method_or_key)) {

            return $this->{$method_or_key}();
        } else {
            return $this->before_method($method_or_key);
        }
    }

    /**
     * @param Context $context
     *
     * @return mixed
     */
    public function context($context = null) {
        if ($context) {
            $this->context = $context;
            return;
        }

        return $this->context();
    }

    /**
     * @return bool
     */
    public function has_key() {
        return true;
    }

    /**
     * @return string
     */
    public function inspect() {
        return __CLASS__;
    }

    /**
     * @return $this
     */
    public function to_liquid() {
        return $this;
    }

    /**
     * @return string
     */
    public function __toString() {
        return __CLASS__;
    }

    /**
     * \ArrayAccess
     */
    public function offsetExists($offset) {
        return true;
    }

    /**
     * \ArrayAccess
     */
    public function offsetSet($key, $value) {
        return;
    }

    /**
     * \ArrayAccess
     */
    public function offsetUnset($key) {
        return;
    }

    /**
     * alias :[] :invoke_drop
     * \ArrayAccess
     *
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key) {
        return $this->invoke_drop($key);
    }

    /**
     * @param string $method_name
     *
     * @return bool
     */
    private function is_invokable($method_name) {
        if (!$this->invokable_methods) {
            $reflection = new \ReflectionClass('\Liquid\Drop');
            $blacklist = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

            $reflection = new \ReflectionClass(get_class($this));
            $public = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach($public as &$method){
                $method = $method->getName();
            }
            foreach($blacklist as &$method) {
                $method = $method->getName();
            }

            $this->invokable_methods = $this->invokable_methods_map + array_diff($public, $blacklist);
        }

        if (isset($this->invokable_methods_map[$method_name])) {
            return true;
        }

        return in_array($method_name, $this->invokable_methods) && (method_exists($this, $method_name) || is_callable(array($this, $method_name)));
    }
}

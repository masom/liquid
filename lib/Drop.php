<?php

namespace Liquid;

class Drop implements \ArrayAccess {
    const EMPTY_STRING = '';

    public function before_method($method) {
        return null;
    }

    public function invoke_drop($method_or_key) {
        if ($method_or_key && $method_or_key != static::EMPTY_STRING && $this->isInvokable($method_or_key)) {
            return $this->{$method_or_key}();
        } else {
            return $this->before_method($method_or_key);
        }
    }

    public function has_key() {
        return true;
    }

    public function inspect() {
        return __CLASS__;
    }

    public function to_liquid() {
        return $this;
    }

    public function __toString() {
        return __CLASS__;
    }

    /**
     * ArrayAccess
     */
    public function offsetExists() {
        return true;
    }
    /**
     * ArrayAccess
     */
    public function offsetSet($key, $value) {
        return;
    }

    /**
     * ArrayAccess
     */
    public function offsetGet($key) {
        return $this->invoke_drop($key);
    }

    public function isInvokable($method_name) {
        if (!$this->invokable_methods) {
            $reflection = new \ReflectionClass('\Liquid\Drop');
            $blacklist = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

            $reflection = new \ReflectionClass(__CLASS__);
            $public = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

            $this->invokable_methods = array_diff($public, $blacklist);
        }

        return in_array($this->invokable_methods, $method_name) && method_exists($this, $method_or_key) || is_callable(array($this, $method_or_key));
    }
}

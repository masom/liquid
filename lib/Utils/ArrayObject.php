<?php

namespace Liquid\Utils;

class ArrayObject implements \ArrayAccess, \Iterator {

    /** @var \ArrayObject */
    protected $array;

    public function __construct(array $array = array()) {
        $this->array = new \ArrayObject();

        foreach($array as $item) {
            $this->array[] = new \ArrayObject($item);
        }
    }

    public function &current() {
        return $this->array[key($this->array)];
    }

    public function next() {
        next($this->array);
    }

    public function key() {
        return key($this->array());
    }

    public function valid() {
        $key = key($this->array);
        if (!$key) {
            return false;
        }
        return isset($this->array[$key]);
    }

    public function rewind() {
        reset($this->array);
    }

    public function &last() {
        if (!$this->array) {
            $e = null;
            return $e;
        }

        foreach($this->array as &$env) {
        }

        return $env;
    }

    public function &offsetGet($offset) {
        if (!isset($this->array[$offset])) {
            $this->array[$offset] = new \ArrayObject();
        }

        $ref =& $this->array[$offset];
        return $ref;
    }

    public function offsetSet($offset, $value) {
        $this->array[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->array[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->array[$offset]);
    }
}

<?php

namespace Liquid\Utils;

class ArrayObject implements \ArrayAccess, \Iterator {

    /** @var \ArrayObject */
    protected $array;

    protected $position = 0;

    public function __construct(array $array = array()) {
        $this->array = new \ArrayObject();

        foreach($array as $item) {
            $this->array[] = new \ArrayObject($item);
        }
    }

    public function &current() {
        return $this->array[$this->position];
    }

    public function next() {
        $this->position++;
    }

    public function key() {
        return $this->position;
    }

    public function valid() {
        return isset($this->array[$this->position]);
    }

    public function rewind() {
        $this->position = 0;
    }

    public function &last() {
        if (!$this->array) {
            $e = null;
            return $e;
        }

        $env =& $this->array[$this->array->count() - 1];

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
        if ($offset) {
            $this->array[$offset] = $value;
        } else {
            $this->array[] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->array[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->array[$offset]);
    }
}

<?php

namespace Liquid\Utils;

class ArrayObject implements \ArrayAccess, \Iterator, \Countable {

    /** @var \ArrayObject */
    protected $array;

    protected $position = 0;

    public function __construct(array $array = array()) {
        $this->array = $array;
    }

    public function current() {
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

    public function merge($additional) {
        foreach($additional as $k => $v) {
            if (is_numeric($k)) {
                $this->array[] = $v;
            } else {
                $this->array[$k] = $v;
            }
        }

        return $this->array;
    }

    public function count() {
        if (is_array($this->array)) {
            return count($this->array);
        }

        return $this->array->count();
    }

    public function last() {
        if (!$this->array) {
            $e = null;
            return $e;
        }

        if (is_object($this->array)) {
            return $this->array[$this->array->count() - 1];
        }

        if (is_array($this->array)) {
            end($this->array);
            $return =& $this->array[key($this->array)];
            reset($this->array);
            return $return;
        }
    }

    public function offsetGet($offset) {
        return isset($this->array[$offset]) ? $this->array[$offset] : null;
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

    public function pop() {
        $array = new \ArrayObject();

        $i = 0;
        foreach($this->array as $a) {

            if( $i == 0) {
                $return = $a;
                $i++;
                continue;
            }

            $array[] = $a;
            $i++;
        }

        $this->array = $array;
        return $return;
    }
}

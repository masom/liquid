<?php

namespace Liquid\Utils;

class Registers implements \ArrayAccess {

    /** @var array */
    protected $registers;

    public function __construct(array $registers = array()) {
        $this->registers = $registers;
    }

    public function last() {
        return end($this->registers);
    }

    public function offsetGet($offset) {
        if (!isset($this->registers[$offset])) {
            $this->registers[$offset] = array();
        }

        return $this->registers[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->registers[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->registers[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->registers[$offset]);
    }
}

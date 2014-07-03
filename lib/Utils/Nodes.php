<?php

namespace Liquid\Utils;

class Nodes implements \ArrayAccess {

    protected $nodes;

    public function __construct(array $nodes = array()) {
        $this->nodes = $nodes;
    }

    public function nodes() {
        return $this->nodes;
    }

    public function offsetGet($offset) {
        return $this->nodes[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->nodes[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->nodes[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->nodes[$offset]);
    }
}

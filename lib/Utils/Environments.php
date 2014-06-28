<?php

namespace Liquid\Utils;

class Environments implements \ArrayAccess {
    /** @var array */
    protected $environments;

    public function __construct(array $environments = array()) {
        $this->environments = $environments;
    }

    public function offsetGet($offset) {
        if (!isset($this->environments[$offset])) {
            $this->environments[$offset] = array();
        }

        return $this->environments[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->environments[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->environments[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->environments[$offset]);
    }
}

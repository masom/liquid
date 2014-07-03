<?php

namespace Liquid\Utils;

class Tokens extends \ArrayObject {

    protected $tokens;

    public function __construct(array $tokens = array()) {
        $this->tokens = $tokens;
    }

    public function tokens() {
        return $this->tokens;
    }

    public function shift() {
        return array_shift($this->tokens);
    }

    public function offsetGet($offset) {
        return $this->array[$offset];
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

<?php

namespace Liquid\Utils;

class Environments extends \Liquid\Utils\ArrayObject {
    public function offsetGet($offset) {
        if (!isset($this->array[$offset])) {
            $this->array[$offset] = new \ArrayObject();
        }

        return $this->array[$offset];
    }
}

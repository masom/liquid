<?php

namespace Liquid\Utils;

class Registers extends \Liquid\Utils\ArrayObject {
    public function __construct(array $array = array()) {
        $this->array = new \ArrayObject();

        foreach($array as $item) {
            $this->array[] = new \ArrayObject($item);
        }
    }

    public function offsetGet($offset) {
        if (!isset($this->array[$offset])) {
            $this->array[$offset] = new \ArrayObject();
        }

        return $this->array[$offset];
    }
}

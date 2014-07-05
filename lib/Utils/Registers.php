<?php

namespace Liquid\Utils;

class Registers extends \Liquid\Utils\ArrayObject {
    public function __construct(array $array = array()) {
        $this->array = new \ArrayObject();

        foreach($array as $item) {
            $this->array[] = new \ArrayObject($item);
        }
    }
}

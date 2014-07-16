<?php

namespace Liquid\Utils;

use \Liquid\Utils\ArrayObject;


class Scopes extends ArrayObject {

    /**
     * @param array $array
     */
    public function __construct(array $array = array()) {
        $this->array = new ArrayObject();

        foreach($array as $item) {
            $this->array[] = is_array($item) ? new \ArrayObject($item) : $item;
        }
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset) {
        if (!isset($this->array[$offset])) {
            $this->array[$offset] = new \ArrayObject();
        }

        return $this->array[$offset];
    }

    /**
     * @param $new_scopes
     *
     * @return $this|void
     */
    public function merge($new_scopes) {
        $scope = $this->array[0];

        foreach($new_scopes as $k => $v) {
            if (is_numeric($k)) {
                $scope[] = $v;
            } else {
                $scope[$k] = $v;
            }
        }
    }
}

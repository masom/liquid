<?php

namespace Liquid\Utils;

use \Liquid\Utils\ArrayObject;

class Scopes extends ArrayObject {
    public function __construct(array $array = array()) {
        $this->array = new ArrayObject();

        foreach($array as $item) {
            $this->array[] = new \ArrayObject($item);
        }
    }

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

    public function push($new_scope) {
        $scopes = new ArrayObject();
        $scopes[] = $new_scope instanceof \ArrayObject ? $new_scope : new \ArrayObject($new_scope);

        foreach($this->array as $scope) {
            $scopes[] = $scope;
        }

        $this->array = $scopes;
    }

    public function count() {
        return $this->array->count();
    }
}

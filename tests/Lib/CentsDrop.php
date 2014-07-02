<?php

namespace Liquid\Tests\Lib;

use \Liquid\Tests\Lib\HundredCentes;

class CentsDrop extends \Liquid\Drop {
    public function amount() {
        return new HundredCentes();
    }

    public function __call($method, $args) {
        if ($method == 'non_zero?') {
            return true;
        }
        throw new \BadMethodCallException("Method `{$method}` is undefined.");
    }
}

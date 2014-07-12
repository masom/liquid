<?php

namespace Liquid\Tests\Lib;

use \Liquid\Tests\Lib\HundredCentes;

class CentsDrop extends \Liquid\Drop {

    protected $invokable_methods_map = array(
        'non_zero?' => 'is_non_zero'
    );

    public function amount() {
        return new HundredCentes();
    }

    public function is_non_zero() {
            return true;
    }
}

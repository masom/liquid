<?php

namespace Liquid\Tests\Lib;

class CounterDrop extends \Liquid\Drop {
    protected $count = 0;

    public function count() {
        $this->count++;
        return $this->count;
    }
}

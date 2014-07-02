<?php

namespace Liquid\Tests\Lib;

class ContextSensitiveDrop extends \Liquid\Drop {

    public function test() {
        return $this->context['test'];
    }
}

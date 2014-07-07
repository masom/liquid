<?php

namespace Liquid\Tests\Lib;

class GlobalFilter {
    public function notice($output) {
        return "Global {$output}";
    }
}

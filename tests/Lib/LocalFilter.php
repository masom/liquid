<?php

namespace Liquid\Tests\Lib;

class LocalFilter {
    public function notice($output) {
        return "Local {$output}";
    }
}

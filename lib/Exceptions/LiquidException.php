<?php

namespace Liquid\Exceptions;

class LiquidException extends \RuntimeException {

    public function setLine($line) {
        $this->line = $line;
    }
    public function setFile($file) {
        $this->file = $file;
    }
}

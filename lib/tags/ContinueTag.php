<?php

namespace Liquid\Tags;

use \Liquid\Interrupts\ContinueInterrupt;

class ContinueTag extends \Liquid\Tag {

    public function interrupt() {
        return new ContinueInterrupt();
    }
}

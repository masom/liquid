<?php
namespace Liquid\Tags;

use \Liquid\Interrupts\BreakInterrupt;

class BreakTag extends \Liquid\Tag {

    public function interrupt() {
        return new BreakInterrupt();
    }
}

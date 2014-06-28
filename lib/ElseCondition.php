<?php

namespace Liquid;

use \Liquid\Condition;

class ElseCondition extends Condition {
    public function isElse() {
        return true;
    }

    public function evaluate($context) {
        return true;
    }
}

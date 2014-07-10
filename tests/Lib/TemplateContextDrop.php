<?php


namespace Liquid\Tests\Lib;


class TemplateContextDrop extends \Liquid\Drop {
    public function before_method($method) {
        return $method;
    }

    public function foo() {
        return 'fizzbuzz';
    }

    public function baz() {
        $registers = $this->context->registers();
        return $registers['lulz'];
    }
}

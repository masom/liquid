<?php


namespace Liquid\Tests\Lib;


class ArrayDrop {

    public function __construct(array $array) {
        $this->array = $array;
    }

    public function each(\Closure $block) {
        foreach($this->array as $item) {
            $block($item);
        }
    }
}

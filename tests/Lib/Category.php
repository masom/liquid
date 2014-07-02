<?php

namespace Liquid\Tests\Lib;

use \Liquid\Tests\Lib\CategoryDrop;

class Category extends \Liquid\Drop {
    protected $name;

    public function name($name = null) {
        if ($name) {
            $this->name = $name;
        }
        return $this->name;
    }

    public function to_liquid() {
        return new CategoryDrop($this);
    }
}

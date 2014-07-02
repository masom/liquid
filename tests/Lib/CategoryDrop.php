<?php

namespace Liquid\Tests\Lib;

class CategoryDrop {
    protected $category;

    public function context($context = null) {
        if ($context) {
            $this->context = $context;
            return;
        }
        return $this->context;
    }

    public function category($category = null) {
        if ($category) {
            $this->category = $category;
            return;
        }
        return $this->category;
    }

    public function __construct($category) {
        $this->category = $category;
    }
}

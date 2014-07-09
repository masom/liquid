<?php

namespace Liquid\Tags;

class Comment extends \Liquid\Block {

    public function render(&$context) {
        return '';
    }

    public function unknown_tag($tag, $markup, $tokens) {
    }

    public function is_blank() {
        return true;
    }
}

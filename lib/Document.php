<?php

namespace Liquid;


class Document extends \Liquid\Block {

    public static function parse($tokens, array $options = array()) {
        return parent::parse(null, null, $tokens, $options);
    }

    public function block_delimiter() {
        return array();
    }

    public function assert_missing_delimitation() {
    }
}

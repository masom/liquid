<?php

namespace Liquid;


class Document extends \Liquid\Block {

    public static function __callStatic($method, $args) {
        if ($method == 'parse') {
            $tokens = $args[0];
            $options = $args[1];
            return parent::parse(null, null, $tokens, $options);
        }
        throw new \BadMethodCallException();
    }

    public function block_delimiter() {
        return array();
    }

    public function assert_missing_delimitation() {
    }
}

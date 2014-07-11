<?php


namespace Liquid\Tests\Lib;


class SubstituteFilter {

    public function substitute($input, $params = array()) {
        return preg_replace_callback(
            '/%\{(\w+)\}/',
            function($match) use ($params) {
                return $params[$match[1]];
            },
            $input
        );
    }
}

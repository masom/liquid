<?php

namespace Liquid\Utils;

class Arrays {
    /**
     * @see http://stackoverflow.com/a/1320259/1014879
     */
    public static function flatten(array $array) {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
        return iterator_to_array($array, true);
    }
}

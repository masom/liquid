<?php

namespace Liquid\Utils;

class Arrays {
    /**
     * http://stackoverflow.com/a/1320156/1014879
     */
    public static function flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }
}

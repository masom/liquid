<?php

namespace Liquid\Utils;

class Arrays {

    /**
     * Flatten an array
     * @see http://stackoverflow.com/a/1320156/1014879
     * @param array $array
     * @return array
     */
    public static function flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }


    /**
     * Check if an array is associative.
     *
     * @see http://stackoverflow.com/a/4254008/1014879
     * @param array $array
     *
     * @return bool
     */
    public static function is_assoc(array& $array) {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Return the first item of an iterator.
     *
     * @param array|\ArrayObject|\Iterator $array
     *
     * @return mixed|null
     */
    public static function first( $array) {
        foreach($array as $item) {
            return $item;
        }
        return null;
    }
}

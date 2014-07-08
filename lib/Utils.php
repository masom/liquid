<?php

namespace Liquid;

class Utils {

    /**
     * @param $collection
     * @param $from
     * @param $to
     *
     * @return array
     */
    public static function slice_collection($collection, $from, $to) {
        if (($from !=0 || $to != null) && is_array($collection)) {
            return array_slice($collection, $from, $to);
        } else {
            return static::slice_collection_using_each($collection, $from, $to);
        }
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    public static function is_non_blank_string($collection) {
        return (is_string($collection) && $collection != '');
    }

    /**
     * @param $collection
     * @param $from
     * @param $to
     *
     * @return array
     */
    public static function slice_collection_using_each($collection, $from, $to) {
        $segments = array();
        $index = 0;

        if (static::is_non_blank_string($collection)) {
            return array($collection);
        }

        foreach($collection as $item) {
            if ($to && $to <= $index) {
                break;
            }

            if ($from <= $index) {
                $segments[] = $item;
            }

            $index++;
        }

        return $segments;
    }
}

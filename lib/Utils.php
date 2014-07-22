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
        $fromTo = ($from !=0 || $to != null);
        if ($fromTo && is_array($collection)) {
            return array_slice($collection, $from, abs($to - $from));
        } elseif($fromTo && is_object($collection) && method_exists($collection, 'load_slice')) {
            return $collection->load_slice($from, $to);
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

        if (is_object($collection) && method_exists($collection, 'each')) {
            $stop = false;
            $collection->each(function($item) use ($from, $to, &$index, &$segments, &$stop) {
                if ($to && $to <= $index) {
                    $stop = true;
                    return;
                }

                if ($from <= $index) {
                    $segments[] = $item;
                }

                $index++;
            }, $stop );

            return $segments;
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

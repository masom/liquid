<?php

namespace Liquid;

use \Liquid\Utils\Arrays;

class StandardFilters {
    protected static $HTML_ESCAPE = array(
        '&' => '&amp;',
        '>' => '&gt;',
        '<' => '&lt;',
        '"' => '&quot;',
        "'" => '&39;'
    );

    protected static $METHOD_MAP = array(
        'default' => 'defaultFunction'
    );

    const HTML_ESCAPE_ONCE_REGEXP = '/["><\']|&(?!([a-zA-Z]+|(#\d+));)/';

    function __call( $name, $args )
    {
        if (isset(static::$METHOD_MAP[$name])) {
            $method = static::$METHOD_MAP[$name];
            switch(count($args)) {
                case 0:
                    return $this->{$method}();
                case 1:
                    return $this->{$method}($args[0]);
                case 2:
                    return $this->{$method}($args[0], $args[1]);
                case 3:
                    return $this->{$method}($args[0], $args[1], $args[2]);
                case 4:
                    return $this->{$method}($args[0], $args[1], $args[2], $args[3]);
                case 5:
                    return $this->{$method}($args[0], $args[1], $args[2], $args[3], $args[4]);
                default:
                    return call_user_func_array( array( $this, $method ), $args );
                    break;
            }
        }
        throw new \BadMethodCallException("Method `{$name}` is not defined.");
    }


    public function size($input) {
        if (is_array($input) || $input instanceof \Countable) {
            return count($input);
        }

        if (is_string($input)) {
            return mb_strlen($input);
        }

        return 0;
    }

    public function downcase($input) {
        return mb_strtolower((string) $input);
    }

    public function upcase($input) {
        return mb_strtoupper((string) $input);
    }

    public function capitalize($input) {
        return mb_convert_case($input, MB_CASE_TITLE);
    }

    public function escape($input) {
        return htmlentities($input, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
    }

    public function escape_once($input) {
        return preg_replace(static::HTML_ESCAPE_ONCE_REGEXP, static::$HTML_ESCAPE, $input);
    }

    /**
     * Alias of escape
     */
    public function h($input) {
        return $this->escape($input);
    }

    public function truncate($input, $length = 50, $truncate_string = '...') {
        if ($input == null) {
            return null;
        }

        $l = ((int) $length) - mb_strlen($truncate_string);
        $l = $l < 0 ? 0 : $l;

        if (mb_strlen($input) > $length) {
            return mb_strcut($input, 0, $l) . $truncate_string;
        } else {
            return $input;
        }
    }

    public function truncatewords($input, $words = 15, $truncate_string = '...') {
        if ($input == null) {
            return null;
        }

        $wordlist = mb_split('\s', $input);
        $l = ((int) $words) - 1;
        $l = $l < 0 ? 0 : $l;

        if (count($wordlist) > $l) {
            return implode(' ', array_slice($wordlist, 0, $l))  . $truncate_string;
        } else {
            return $input;
        }
    }

    public function split($input, $pattern) {

        //TODO Figure out why pattern comes in as an array
        return explode($pattern[0], $input);
    }

    public function strip($input) {
        return trim($input);
    }

    public function lstrip($input) {
        return ltrim($input);
    }

    public function rstrip($input) {
        return rtrim($input);
    }

    public function strip_html($input) {
        $patterns = array(
            '/<script.*?<\/script>/s',
            '/<!--.*?-->/s',
            '/<style.*?<\/style>/s',
            '/<.*?>/s'
        );
        return preg_replace($patterns, '', $input);
    }

    public function strip_newlines($input) {
        return preg_replace('/\r?\n/', '', $input);
    }

    public function join($input, $glue = ' ') {
        if ($input instanceof \ArrayObject ){
            $input = $input->getArrayCopy();
        }

        if (empty($glue)) {
            $glue = ' ';
        }
        return implode($glue, Arrays::flatten(array($input)));
    }

    /**
     * @param array $input
     * @param string $property
     *
     * @return array
     */
    public function sort($input, $property = null) {
        if ($input instanceof \ArrayObject){
            $input = $input->getArrayCopy();
        }

        $array = $this->flatten_if_necessary($input);

        if ($property == null) {
            sort($array);
        } else {
            $first = reset($array);

            if (is_array($first) || $first instanceof \ArrayAccess) {
                usort($array, function($a, $b) use ($property) {
                    if ($a[$property] == $b[$property]) {
                        return 0;
                    }
                    if ($a[$property] < $b[$property]) {
                        return 1;
                    }
                    return -1;
                } );
            } elseif (method_exists($first, $property)) {
                usort($array, function($a, $b) use ($property) {
                    $aval = $a->{$property}();
                    $bval = $b->{$property}();

                    if ($aval == $bval) {
                        return 0;
                    }

                    if ($aval < $bval) {
                        return 1;
                    }

                    return -1;
                });
            } elseif(property_exists($first, $property)){
                usort($array, function($a, $b) use ($property) {
                    $aval = $a->{$property};
                    $bval = $b->{$property};

                    if ($aval == $bval) {
                        return 0;
                    }

                    if ($aval < $bval) {
                        return 1;
                    }

                    return -1;
                });
            }

        }

        return $array;
    }

    public function reverse($input) {
        $ary = Arrays::flatten(array($input));
        return array_reverse($ary);
    }

    public function map($input, $property) {
        $this->flatten_if_necessary($input, function($e) use ($property) {
            if($e instanceof \Closure || is_callable($e)) {
                $e = $e();
            }

            if ($property == 'to_liquid') {
                return $e;
            } elseif (is_array($e) || $e instanceof \ArrayAccess) {
                return $e[$property];
            }
        });
    }

    public function replace($input, $string, $replacement = '') {
        return preg_replace((string) $string, (string) $replacement, (string) $input);
    }

    public function replace_first($input, $string, $replacement = '') {
        return preg_replace((string) $string, (string) $replacement, (string) $input, 1);
    }

    public function remove($input, $string) {
        return preg_replace((string) $string, '', (string) $input);
    }

    public function remove_first($input, $string) {
        return preg_replace((string) $string, '', (string) $input, 1);
    }

    public function append($input, $string) {
        return (string) $input . (string) $string;
    }

    public function prepend($input, $string) {
        return (string) $string . (string) $input;
    }

    public function newline_to_br($input) {
        return nl2br($input);
    }

    public function date($input, $format = null) {

        if (!$format) {
            return $input;
        }

        if (!($date = $this->to_date($input))) {
            return $input;
        }

        return $date->strftime($format);
    }

    public function first($array) {
        if (!is_array($array)) {
            return null;
        }
        return reset($array);
    }

    public function last($array) {
        if (!is_array($array)) {
            return null;
        }

        return end($array);
    }

    public function plus($input, $operand) {
        if (!is_numeric($input) || !is_numeric($operand)) {
            return null;
        }

        return $input + $operand;
    }

    public function minus($input, $operand) {
        if (!is_numeric($input) || !is_numeric($operand)) {
            return null;
        }

        return $input - $operand;
    }

    public function times($input, $operand) {

        if (!is_numeric($input) || !is_numeric($operand)) {
            return null;
        }

        return $input * $operand;
    }

    public function divided_by($input, $operand) {
        if (!is_numeric($input) || !is_numeric($operand)) {
            return null;
        }

        return $input / $operand;
    }

    public function modulo($input, $operand) {
        if (!is_numeric($input) || !is_numeric($operand)) {
            return null;
        }

        return $input % $operand;
    }

    public function round($input, $n = 0) {
       return round($input, $n);
    }

    public function ceil($input) {
        return ceil($input);
    }

    public function floor($input) {
        return floor($input);
    }

    /**
     * Was default. Reserved keyword.
     * TODO method map
     */
    public function defaultFunction($input, $default_value = '') {
        $is_blank = empty($input);

        return empty($input) ? $default_value : $input;
    }

    private function flatten_if_necessary($input) {
        if (is_array($input)) {
            $ary = Arrays::flatten($input);
        } else {
            $ary = Arrays::flatten(array($input));
        }

        foreach($ary as $key => &$value) {
            if (is_object($value) && method_exists($value, 'to_liquid')) {
                $value = $value->to_liquid();
            }
        }

        return $ary;
    }

    /**
     * @param mixed $obj
     * @return \DateTime
     */
    private function to_date($obj) {
        if( $obj instanceof \DateTime || method_exists($obj, 'format')) {
            return $obj;
        }

        if ($obj === 'now' || $obj === 'today') {
            return new \DateTime();
        }

        try{
            return new \DateTime($obj);
        } catch(\Exception $e) {
            return null;
        }
    }
}

<?php


namespace Liquid\Utils;


class InputIterator
{

    /**
     * @param mixed $input
     */
    public function __construct($input)
    {
        switch (true) {
            case $input instanceof \Iterator || $input instanceof \ArrayAccess || $input instanceof \ArrayObject:
                $this->array = $input;
                break;

            case is_array($input):
                if (Arrays::is_assoc($input)) {
                    $this->array = array($input);
                } else {
                    $this->array = Arrays::flatten($input);
                }
                break;

            default:
                $this->array = array($input);
        }
    }

    /**
     * @param string $glue
     *
     * @return string
     */
    public function join($glue)
    {
        if (is_object($this->array)) {
            if ($this->array instanceof \ArrayObject) {
                return implode($glue, $this->array->getArrayCopy());
            }

            $result = '';

            foreach ($this->array as $item) {
                $result .= $glue . $item;
            }

            return $result;
        } else {
            return implode($glue, $this->array);
        }
    }

    /**
     * @return array
     */
    public function reverse()
    {
        if (is_array($this->array)) {
            return array_reverse($this->array);
        }

        if ($this->array instanceof \ArrayObject) {
            return array_reverse($this->array->getArrayCopy());
        }

        $result = array();
        foreach ($this->array as $item) {
            $result[] = $item;
        }

        return array_reverse($result);
    }

    /**
     * @param callable $closure
     */
    public function each(\Closure $closure)
    {
        foreach ($this->array as $e) {
            if (is_object($e) && method_exists($e, 'to_liquid')) {
                $closure($e->to_liquid);
            } else {
                $closure($e);
            }
        }
    }

    /**
     * @return mixed
     */
    public function to_array()
    {
        if (is_array($this->array)) {
            return $this->array;
        }

        if ($this->array instanceof \ArrayObject) {
            return $this->array->getArrayCopy();
        }

        $result = array();
        foreach ($this->array as $item) {
            $result[] = $item;
        }

        return $result;
    }
}

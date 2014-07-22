<?php


namespace Liquid\Tests\Lib;


use Liquid\Drop;


class LoaderDrop extends Drop {

    protected $each_called = false;
    protected $load_slice_called = false;

    protected $data;

    /**
    * @return bool
     */
    public function each_called() {
        return $this->each_called;
    }

    public function load_slice_called() {
        return $this->load_slice_called;
    }

    /**
    * @param $data
     */
    public function __construct(array $data = array()) {
        $this->data = $data;
    }

    /**
     * TODO Document this behaviour
     *
     * Ideally implementing Iterator should be easier :(
     * @param callable $closure
     * @param boolean &$stop Stop the iteration when the value becomes true.
     */
    public function each(\Closure $closure, &$stop) {
        $this->each_called = true;

        foreach($this->data as $el){
            if ($stop) {
                break;
            }

            $closure($el);
        }
    }

    /**
     * @param $from
     * @param $to
     *
     * @return array
     */
    public function load_slice($from, $to) {
        $this->load_slice_called = true;
        $length = $to - $from;
        return array_slice($this->data, $from, $length);
    }
}

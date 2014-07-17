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
    public function initialize($data) {
        $this->data = $data;
    }

    /**
     * @param callable $closure
     */
    public function each(\Closure $closure) {
        $this->each_called = true;
        foreach($this->data as $el){
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
        return array_slice($this->data, $from, $to - 1);
    }
}

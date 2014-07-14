<?php


namespace Liquid\Tests\Lib;


class InfiniteFileSystem {

    protected $response = "-{% include 'loop' %}";

    public function set_response($response) {
        $this->response = $response;
    }

    public function read_template_file($template_path, $context) {
        return $this->response;
    }
}

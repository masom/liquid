<?php


namespace Liquid\Tests\Lib;


class CountingFileSystem {
    protected $count = 0;

    public function read_template_file($template_path, $context) {
        $this->count++;

        return 'from CountingFileSystem';
    }


    public function count() {
        return $this->count;
    }
}

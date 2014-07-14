<?php


namespace Liquid\Tests\Lib;


class OtherFileSystem {
    public function read_template_file($template_path, $context) {
        return 'from OtherFileSystem';
    }
}

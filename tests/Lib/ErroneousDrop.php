<?php


namespace Liquid\Tests\Lib;


class ErroneousDrop extends \Liquid\Drop {

    public function bad_method() {
        throw new \Exception('ruby error in drop');
    }
}

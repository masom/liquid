<?php


namespace Liquid\Tests\Lib;


class CanadianMoneyFilter {

    public function money($input) {
        return sprintf(' %d$ CAD ', $input);
    }
}

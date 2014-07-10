<?php


namespace Liquid\Tests\Lib;


class MoneyFilter {
    public function money($input){
        return sprintf(' %d$ ', $input);
    }

    public function money_with_underscore($input) {
        return sprintf(' %d$ ', $input);
    }
}

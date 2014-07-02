<?php

namespace Liquid\Tags;

use Liquid\Liquid;


class Raw extends \Liquid\Block {

    protected static $FullTokenPossiblyInvalid;

    public static function init() {
        static::$FullTokenPossiblyInvalid = '/\A(.*)' . Liquid::TagStart . '\s*(\w+)\s*(.*)?' . Liquid::TagEnd .'\z/om';
    }

    public function parse($tokens) {
        $this->nodelist = array();

        while($token = array_shift($tokens)) {
            $matches = null;
            if (preg_match(static::$FullTokenPossiblyInvalid, $token, $matches)) {
                if ($matches[1] != '') {
                    $this->nodelist[] = $matches[1];
                }
                if ($this->block_delimiter() == $matches[2]) {
                    $this->end_tag();
                    return;
                }
            }

            if (!empty($token)) {
                $this->nodelist[] = $token;
            }
        }
    }
}

Raw::init();

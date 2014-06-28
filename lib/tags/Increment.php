<?php

namespace Liquid\Tags;

class Increment extends \Liquid\Tag {

    public function __construct($tag_name, $markup, $options) {
        parent::__construct($tag_name, $markup, $options);

        $this->variable = trim($markup);
    }

    public function render($context) {
        $environment = reset($context->environments());
        $value = (int) $environment[$this->variable] || 0;
        $environment[$this->variable] = $value + 1;

        return (string) $value;
    }

    public function is_blank() {
        return false;
    }
}

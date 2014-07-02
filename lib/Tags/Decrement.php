<?php

namespace Liquid;

class Decrement extends \Liquid\Tag {

    protected $variable;

    public function __construct($tag_name, $markup, $options) {
        parent::__construct($tag_name, $markup, $options);

        $this->variable = trim($markup);
    }

    public function render($context) {
        $environment = reset($context->environments());

        $value = $environment[$this->variable];
        $value--;
        $environment[$this->variable] = $value;

        return (string) $value;
    }
}

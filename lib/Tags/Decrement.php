<?php

namespace Liquid;

class Decrement extends \Liquid\Tag {

    protected $variable;

    /**
     * @param string $tag_name
     * @param string $markup
     * @param array  $options
     */
    public function __construct($tag_name, $markup, $options) {
        parent::__construct($tag_name, $markup, $options);

        $this->variable = trim($markup);
    }

    /**
     * @param Context $context
     *
     * @return string
     */
    public function render($context) {
        $environments = $context->environments();
        $environment = $environments[0];

        $value = $environment[$this->variable];
        $value--;
        $environment[$this->variable] = $value;

        return (string) $value;
    }
}

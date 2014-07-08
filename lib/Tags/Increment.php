<?php

namespace Liquid\Tags;

class Increment extends \Liquid\Tag {

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
     * @param \Liquid\Context $context
     *
     * @return null|string
     */
    public function render($context) {
        $environment = reset($context->environments());
        $value = (int) $environment[$this->variable] || 0;
        $environment[$this->variable] = $value + 1;

        return (string) $value;
    }

    /**
     * @return bool
     */
    public function is_blank() {
        return false;
    }
}

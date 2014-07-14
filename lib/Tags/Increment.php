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
     * @return string
     */
    public function render(&$context) {
        $environments = $context->environments();
        $env = $environments[0];

        if (!isset($env[$this->variable])) {
            $env[$this->variable] = 0;
        }

        $variable = $env[$this->variable];
        $env[$this->variable] += 1;

        return (string) $variable;
    }

    /**
     * @return bool
     */
    public function is_blank() {
        return false;
    }
}

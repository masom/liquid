<?php

namespace Liquid\Tags;

use Liquid\Context;


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
    public function render(&$context) {
        $environments = $context->environments();
        $env =& $environments[0];

        if (!isset($env[$this->variable])) {
            $env[$this->variable] = 0;
        }

        $env[$this->variable] -= 1;

        $variable = $env[$this->variable];

        return (string) $variable;
    }
}

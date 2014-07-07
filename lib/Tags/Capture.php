<?php

namespace Liquid\Tags;

class Capture extends \Liquid\Block {
    const Syntax = '/(\w+)/';

    protected $to;

    public function __construct($tag_name, $markup, $options) {
        parent::__construct($tag_name, $markup, $options);

        $matches = null;
        if (preg_match(static::Syntax, $markup, $matches)) {
            $this->to = $matches[1];
        } else {
            throw new \Liquid\Exceptions\SyntaxError("Syntax Error in 'capture' - Valid syntax: capture [var]");
        }
    }

    public function render($context) {
        $output = parent::render($context);

        $context->scopes_last_set($this->to, $output);

        $context->increment_used_resources('assigns_score_current', $output);

        return null;
    }

    public function is_blank() {
        return true;
    }
}

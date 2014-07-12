<?php

namespace Liquid\Tags;

use Liquid\Context;


class IfChanged extends \Liquid\Block {

    /**
     * @param \Liquid\Context $context
     *
     * @return null|string
     */
    public function render(&$context) {
        $nodelist =& $this->nodelist;
        $output = null;
        $self = $this;
        $context->stack(function($context) use ($self, &$nodelist, &$output) {
            /** @var Context $context */
            $output = $self->render_all($nodelist, $context);
            $registers = $context->registers();

            if ($output != $registers['ifchanged']) {
                $registers['ifchanged'] = $output;
            } else {
                $output = '';
            }
        });

        return $output;
    }
}

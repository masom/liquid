<?php

namespace Liquid\Tags;


class Unless extends \Liquid\Tags\IfTag {

    public function render($context) {
        $self = $this;
        $blocks =& $this->blocks;
        $return = '';
        $context->stack(function($context) use ($self, &$blocks, &$return) {
            $first_block = reset($this->blocks);

            if( !$first_block->evaluate($context)) {
                $return = $self->render_all($first_block->attachment(), $context);
                return;
            }

            //TODO check other array_slice to have -2 instead of -1
            foreach(array_slice($blocks, 1, count($blocks) - 2 ) as $block) {
                if ($block->evaluate($context)) {
                    $return = $self->render_all($block->attachment(), $context);
                    return;
                }
            }
        });

        return $return;
    }
}

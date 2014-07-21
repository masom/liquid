<?php

namespace Liquid\Tags;


use Liquid\Utils\Arrays;


class Unless extends \Liquid\Tags\IfTag {

    /**
     * @param \Liquid\Context $context
     *
     * @return string
     */
    public function render(&$context) {
        $self = $this;
        $blocks =& $this->blocks;
        $return = '';
        $context->stack(function($context) use ($self, &$blocks, &$return) {

            /** @var \Liquid\Condition $first_block */
            $first_block = Arrays::first($blocks);

            if (!$first_block->evaluate($context)) {
                $return = $self->render_all($first_block->attachment(), $context);
                return;
            }

            $max = count($blocks) - 2 ;
            $i = 0;
            foreach ($blocks as $block) {
                if ($i < 1 || $i < $max ) {
                    $i ++;
                    continue;
                }

                $i ++;

                /** @var \Liquid\Condition $block */
                if ($block->evaluate($context)) {
                    $return = $self->render_all($block->attachment(), $context);
                    return;
                }
            }
        });

        return $return;
    }
}

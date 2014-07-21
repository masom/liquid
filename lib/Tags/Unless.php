<?php

namespace Liquid\Tags;


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
            //TODO Arrays::first() ?
            foreach($blocks as $block) {
                $first_block = reset($blocks);
                break;
            }

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

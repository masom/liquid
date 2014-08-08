<?php

namespace Liquid\Exceptions;

class SyntaxError extends \Liquid\Exceptions\LiquidException
{

    public function setMessage($message)
    {
        $this->message = $message;
    }
}

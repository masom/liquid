<?php

namespace Liquid\Interrupts;

class Interrupt
{

    protected $message;

    public function __construct($message = null)
    {
        $this->message = $message ? : "interrupt";
    }
}

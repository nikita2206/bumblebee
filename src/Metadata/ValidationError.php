<?php

namespace Bumblebee\Metadata;

class ValidationError
{

    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

}

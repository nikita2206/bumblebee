<?php

namespace Bumblebee\Exception;

class InvalidTypeException extends \RuntimeException
{

    public $type;

    public function __construct($type, \Exception $previous = null)
    {
        parent::__construct("Type {$type} doesn't exist or couldn't be found", 0, $previous);

        $this->type = $type;
    }

}

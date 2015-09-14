<?php

namespace Bumblebee\Metadata;

class FunctionMetadata extends TypeMetadata
{

    /**
     * @var string
     */
    protected $function;

    public function __construct($function)
    {
        parent::__construct("function");

        $this->function = $function;
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }
}

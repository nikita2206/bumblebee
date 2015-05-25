<?php

namespace Bumblebee\Compilation;

class FunctionArgument implements Expression
{

    protected $name;

    protected $isReference;

    public function __construct($name, $isReference = false)
    {
        $this->name = $name;
        $this->isReference = $isReference;
    }

    public function generate()
    {
        return ($this->isReference ? "&" : "") . '$' . $this->name;
    }

    /**
     * @return int
     */
    public function evaluationComplexity()
    {
        return 1;
    }
}

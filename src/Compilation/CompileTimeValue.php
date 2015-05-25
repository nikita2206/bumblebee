<?php

namespace Bumblebee\Compilation;


class CompileTimeValue implements Expression
{

    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function generate()
    {
        return var_export($this->value, true);
    }

    public function evaluationComplexity()
    {
        return 1;
    }

}

<?php

namespace Bumblebee\Compilation;

class ConstValue implements Expression, ExpressionMethodCallable, ExpressionCallable, ClassNameConstructable
{

    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function generate()
    {
        return $this->value;
    }

    public function evaluationComplexity()
    {
        return 1;
    }

}

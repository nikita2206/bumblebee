<?php

namespace Bumblebee\Compilation;

class Variable implements Expression, ExpressionMethodCallable, AssignableExpression
{

    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function generate()
    {
        return '$' . $this->name;
    }

}

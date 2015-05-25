<?php

namespace Bumblebee\Compilation;

class Variable
    implements Expression, ExpressionMethodCallable, ExpressionAssignable, ExpressionCallable, ClassNameConstructable, ExpressionDimable
{

    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function generate()
    {
        return '$' . $this->name;
    }

    public function evaluationComplexity()
    {
        return 1;
    }

}

<?php

namespace Bumblebee\Compilation;

class AssignVariable implements Expression
{

    /**
     * @var ExpressionAssignable
     */
    protected $var;

    /**
     * @var Expression
     */
    protected $expression;

    public function __construct(ExpressionAssignable $var, Expression $expression)
    {
        $this->var = $var;
        $this->expression = $expression;
    }

    public function generate()
    {
        return $this->var->generate() . " = " . $this->expression->generate();
    }

}

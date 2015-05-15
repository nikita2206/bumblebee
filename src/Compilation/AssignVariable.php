<?php

namespace Bumblebee\Compilation;

class AssignVariable implements Expression
{

    /**
     * @var AssignableExpression
     */
    protected $var;

    /**
     * @var Expression
     */
    protected $expression;

    public function __construct(AssignableExpression $var, Expression $expression)
    {
        $this->var = $var;
        $this->expression = $expression;
    }

    public function generate()
    {
        return $this->var->generate() . " = " . $this->expression->generate();
    }

}

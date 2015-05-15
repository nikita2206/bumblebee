<?php

namespace Bumblebee\Compilation;


class FetchDim
    implements Expression, ExpressionMethodCallable, ExpressionCallable, AssignableExpression, ClassNameConstructable, ExpressionDimable
{

    /**
     * @var ExpressionDimable
     */
    protected $var;

    /**
     * @var Expression
     */
    protected $dim;

    public function __construct(ExpressionDimable $var, Expression $dim = null)
    {
        $this->var = $var;
        $this->dim = $dim;
    }

    public function generate()
    {
        return $this->var->generate() . "[" . ($this->dim ? $this->dim->generate() : "") . "]";
    }

}

<?php

namespace Bumblebee\Compilation;

class ExpressionStatement extends Statement
{

    /**
     * @var Expression
     */
    protected $expression;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    public function generate()
    {
        return $this->expression->generate() . ";";
    }

}

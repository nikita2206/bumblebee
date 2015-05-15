<?php

namespace Bumblebee\Compilation;


class ReturnStatement extends Statement
{

    /**
     * @var Expression
     */
    protected $expression;

    public function __construct(Expression $expression = null)
    {
        $this->expression = $expression;
    }

    public function generate()
    {
        return "return " . ($this->expression ? $this->expression->generate() : "") . ";";
    }

}

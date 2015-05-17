<?php

namespace Bumblebee\Compilation;


class TernaryExpression implements Expression
{

    /**
     * @var Expression
     */
    protected $condition;

    /**
     * @var Expression
     */
    protected $success;

    /**
     * @var Expression
     */
    protected $failure;

    public function __construct(Expression $condition, Expression $success, Expression $failure)
    {
        $this->condition = $condition;
        $this->success = $success;
        $this->failure = $failure;
    }

    public function generate()
    {
        return $this->condition->generate() . " ? " . $this->success->generate() . " : " . $this->failure->generate();
    }
}

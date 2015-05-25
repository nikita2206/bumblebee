<?php

namespace Bumblebee\Compilation;


class FunctionCall implements Expression, ExpressionMethodCallable, ExpressionDimable
{

    const FUNC_CALL_COMPLEXITY = 7;

    /**
     * @var ExpressionMethodCallable
     */
    protected $function;

    /**
     * @var Expression[]
     */
    protected $arguments;

    public function __construct(ExpressionMethodCallable $function, array $arguments = [])
    {
        $this->function = $function;
        $this->arguments = $arguments;
    }

    public function generate()
    {
        $code = $this->function->generate() . "(";
        $args = [];
        foreach ($this->arguments as $arg) {
            $args[] = $arg->generate();
        }
        return $code . implode(",", $args) . ")";
    }

    public function evaluationComplexity()
    {
        return $this->function->evaluationComplexity() + array_reduce($this->arguments, function ($acc, Expression $arg) {
            return $acc + $arg->evaluationComplexity();
        }, 0);
    }

}

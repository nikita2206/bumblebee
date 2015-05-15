<?php

namespace Bumblebee\Compilation;


class FunctionCall implements Expression, ExpressionMethodCallable, ExpressionDimable
{

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

}

<?php

namespace Bumblebee\Compilation;

class MethodCall implements Expression, ExpressionMethodCallable, ExpressionDimable
{

    const METHOD_CALL_COMPLEXITY = 8;

    /**
     * @var ExpressionMethodCallable
     */
    protected $object;

    /**
     * @var string
     */
    protected $methodName;

    /**
     * @var Expression[]
     */
    protected $args;

    public function __construct(ExpressionMethodCallable $object, $methodName, array $args = [])
    {
        $this->object = $object;
        $this->methodName = $methodName;
        $this->args = $args;
    }

    public function generate()
    {
        $code = $this->object->generate() . "->" . $this->methodName . "(";
        $args = [];
        foreach ($this->args as $arg) {
            $args[] = $arg->generate();
        }
        $code .= implode(",", $args) . ")";

        return $code;
    }

    public function evaluationComplexity()
    {
        return self::METHOD_CALL_COMPLEXITY + array_reduce($this->args, function ($acc, Expression $arg) {
            return $acc + $arg->evaluationComplexity();
        }, 0);
    }

}

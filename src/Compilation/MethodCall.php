<?php

namespace Bumblebee\Compilation;

class MethodCall implements Expression
{

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

}

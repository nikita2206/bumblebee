<?php

namespace Bumblebee\Compilation;

class ConstructObject implements Expression
{

    const OBJECT_NEW_COMPLEXITY = 7;

    /**
     * @var ClassNameConstructable
     */
    protected $className;

    /**
     * @var Expression[]
     */
    protected $arguments;

    public function __construct(ClassNameConstructable $className, array $arguments = [])
    {
        $this->className = $className;
        $this->arguments = $arguments;
    }

    public function generate()
    {
        $code = "new " . $this->className->generate() . "(";
        $args = [];
        foreach ($this->arguments as $arg) {
            $args[] = $arg->generate();
        }
        return $code . implode(",", $args) . ")";
    }

    public function evaluationComplexity()
    {
        return $this->className->evaluationComplexity() + self::OBJECT_NEW_COMPLEXITY + array_reduce($this->arguments, function ($acc, Expression $arg) {
            return $acc + $arg->evaluationComplexity();
        }, 0);
    }

}

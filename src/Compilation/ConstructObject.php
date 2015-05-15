<?php

namespace Bumblebee\Compilation;

class ConstructObject implements Expression
{

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

}

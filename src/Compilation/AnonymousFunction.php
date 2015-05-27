<?php

namespace Bumblebee\Compilation;


class AnonymousFunction implements Expression
{

    /**
     * @var Statement[]
     */
    protected $statements;

    /**
     * @var FunctionArgument[]
     */
    protected $arguments;

    /**
     * @var FunctionArgument[]
     */
    protected $use;

    public function __construct(array $arguments, array $statements, array $use = [])
    {
        $this->arguments = $arguments;
        $this->statements = $statements;
        $this->use = $use;
    }

    public function generate()
    {
        $code = "function(";
        $args = [];
        foreach ($this->arguments as $argument) {
            $args[] = $argument->generate();
        }
        $code .= implode(",", $args) . ") ";

        if ($this->use) {
            $uses = [];
            foreach ($this->use as $use) {
                $uses[] = $use->generate();
            }
            $code .= "use (" . implode(",", $uses) . ") ";
        }

        $stmts = [];
        foreach ($this->statements as $stmt) {
            $stmts[] = $stmt->generate();
        }

        return $code . "{\n" . implode("\n", $stmts) . "\n}";
    }

    public function evaluationComplexity()
    {
        return 1;
    }

}


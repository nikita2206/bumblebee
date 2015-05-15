<?php

namespace Bumblebee\Compilation;


class AnonymousFunction implements Expression
{

    /**
     * @var Statement[]
     */
    protected $statements;

    /**
     * @var Variable[]
     */
    protected $arguments;

    public function __construct(array $arguments, array $statements)
    {
        $this->arguments = $arguments;
        $this->statements = $statements;
    }

    public function generate()
    {
        $code = "function(";
        $args = [];
        foreach ($this->arguments as $argument) {
            $args[] = $argument->generate();
        }
        $code .= implode(",", $args) . ") {\n";

        $stmts = [];
        foreach ($this->statements as $stmt) {
            $stmts[] = $stmt->generate();
        }

        return $code . implode("\n", $stmts) . "\n}";
    }

}

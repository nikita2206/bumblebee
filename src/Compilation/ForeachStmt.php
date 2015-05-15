<?php

namespace Bumblebee\Compilation;


class ForeachStmt implements Statement
{

    /**
     * @var Expression
     */
    protected $traversable;

    /**
     * @var FunctionArgument
     */
    protected $keyVar;

    /**
     * @var FunctionArgument
     */
    protected $valueVar;

    /**
     * @var Statement[]
     */
    protected $statements;

    public function __construct(Expression $traversable, FunctionArgument $valueVar, FunctionArgument $keyVar = null)
    {
        $this->traversable = $traversable;
        $this->keyVar = $keyVar;
        $this->valueVar = $valueVar;
        $this->statements = [];
    }

    public function addStatement(Statement $stmt)
    {
        $this->statements[] = $stmt;
    }

    public function generate()
    {
        $code = "foreach (" . $this->traversable->generate() . " as ";

        if ($this->keyVar) {
            $code .= $this->keyVar->generate() . " => ";
        }

        $code .= $this->valueVar->generate() . ") {\n";

        foreach ($this->statements as $stmt) {
            $code .= $stmt->generate() . "\n";
        }

        return $code . "}";
    }

}

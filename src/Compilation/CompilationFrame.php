<?php

namespace Bumblebee\Compilation;

class CompilationFrame
{

    /**
     * @var Expression
     */
    protected $result;

    /**
     * @var Statement[]
     */
    protected $statements;

    /**
     * @var Expression
     */
    protected $inputData;

    public function __construct(Expression $inputData)
    {
        $this->statements = [];
        $this->inputData = $inputData;
    }

    /**
     * @return Expression
     */
    public function getInputData()
    {
        return $this->inputData;
    }

    /**
     * @param Expression $expression
     */
    public function setResult(Expression $expression)
    {
        $this->result = $expression;
    }

    /**
     * @param Statement $stmt
     */
    public function addStatement(Statement $stmt)
    {
        $this->statements[] = $stmt;
    }

    /**
     * @return Expression
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return Statement[]
     */
    public function getStatements()
    {
        return $this->statements;
    }

}

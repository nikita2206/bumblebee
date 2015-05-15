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

    /**
     * @var string
     */
    protected $type;

    public function __construct(Expression $inputData, $type)
    {
        $this->statements = [];
        $this->inputData = $inputData;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
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

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
     * @param Statement|Expression $stmt
     */
    public function addStatement($stmt)
    {
        if ($stmt instanceof Expression) {
            $stmt = new ExpressionStatement($stmt);
        } elseif ( ! $stmt instanceof Statement) {
            throw new \InvalidArgumentException("CompilationFrame#addStatement only accepts instances of Statement or Expression");
        }

        $this->statements[] = $stmt;
    }

    /**
     * @param Statement[]|Expression[] $statements
     */
    public function addStatements($statements)
    {
        foreach ($statements as $stmt) {
            $this->addStatement($stmt);
        }
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

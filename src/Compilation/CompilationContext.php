<?php

namespace Bumblebee\Compilation;

class CompilationContext
{

    protected $returnExpression;

    /**
     * @var Variable
     */
    protected $inputVariable;

    /**
     * @var Variable
     */
    protected $transformerVariable;

    /**
     * @var CompilationFrame[]
     */
    protected $frameStack;

    /**
     * @var int
     */
    protected $varNameCounter;


    public function __construct(Variable $inputVariable, Variable $transformerVariable)
    {
        $this->inputVariable = $inputVariable;
        $this->transformerVariable = $transformerVariable;
        $this->frameStack = new \SplStack();
        $this->varNameCounter = 0;
    }

    /**
     * @param string $prefix
     * @return Variable
     */
    public function createFreeVariable($prefix = "")
    {
        return new Variable($prefix . "_gen" . $this->varNameCounter++);
    }

    /**
     * @param CompilationFrame $frame
     */
    public function pushFrame(CompilationFrame $frame)
    {
        $this->frameStack->push($frame);
    }

    /**
     * @return CompilationFrame
     */
    public function popFrame()
    {
        return $this->frameStack->pop();
    }

    /**
     * @return CompilationFrame
     */
    public function getCurrentFrame()
    {
        return $this->frameStack->top();
    }

    /**
     * @param Expression $expression
     * @return ExpressionStatement
     */
    public function stateExpression(Expression $expression)
    {
        return new ExpressionStatement($expression);
    }

    /**
     * @param AssignableExpression $var
     * @param Expression $expression
     * @return AssignVariable
     */
    public function assignVariable(AssignableExpression $var, Expression $expression)
    {
        return new AssignVariable($var, $expression);
    }

    /**
     * @param ExpressionMethodCallable $object
     * @param string $methodName
     * @param Variable[] $args
     * @return MethodCall
     */
    public function callMethod(ExpressionMethodCallable $object, $methodName, array $args = [])
    {
        return new MethodCall($object, $methodName, $args);
    }

    /**
     * @param array|string|float|int|bool $value
     * @return ConstValue
     */
    public function constValue($value)
    {
        if (is_resource($value) || is_object($value)) {
            throw new \InvalidArgumentException("Can't create constant from object or resource");
        }

        return new ConstValue($value);
    }

    /**
     * @param Expression $expression
     * @return ReturnStatement
     */
    public function returnStatement(Expression $expression)
    {
        return new ReturnStatement($expression);
    }

    /**
     * @param Variable[] $arguments
     * @param Statement[] $statements
     * @return AnonymousFunction
     */
    public function anonymousFunction(array $arguments, array $statements)
    {
        return new AnonymousFunction($arguments, $statements);
    }

    public function getInputVariable()
    {
        return $this->inputVariable;
    }

    public function getTransformerVariable()
    {
        return $this->transformerVariable;
    }

    /**
     * @param Statement[] $statements
     * @return string
     */
    public function generateCallback(array $statements)
    {
        return $this->anonymousFunction([$this->inputVariable, $this->transformerVariable], $statements)->generate();
    }

}

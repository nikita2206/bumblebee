<?php

namespace Bumblebee\Compilation;

class CompilationContext
{

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

    /**
     * @var Variable[] Keys are types, values are variables with transformers
     */
    protected $recursiveTransformers;


    public function __construct(Variable $inputVariable, Variable $transformerVariable)
    {
        $this->inputVariable = $inputVariable;
        $this->transformerVariable = $transformerVariable;
        $this->frameStack = new \SplStack();
        $this->varNameCounter = 0;
        $this->recursiveTransformers = [];
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
     * @return bool
     */
    public function isCurrentFrameInRecursion()
    {
        /** @var CompilationFrame $top */
        $top = $this->frameStack->pop();
        $isIt = false;

        foreach ($this->frameStack as $frame) {
            if ($frame->getType() === $top->getType()) {
                $isIt = true;
                break;
            }
        }
        $this->frameStack->push($top);

        return $isIt;
    }

    /**
     * @param string $type
     * @param Variable $funcName
     */
    public function addRecursiveTransformer($type, Variable $funcName)
    {
        $this->recursiveTransformers[$type] = $funcName;
    }

    /**
     * @param Variable $funcName
     * @param AnonymousFunction $function
     */
    public function declareRecursiveTransformer(Variable $funcName, AnonymousFunction $function)
    {
        /** @var CompilationFrame $frame */
        $frame = $this->frameStack->bottom();
        $frame->addStatement($this->assignVariable($funcName, $function));
    }

    /**
     * @param string $type
     * @return Variable|null
     */
    public function getRecursiveTransformer($type)
    {
        return isset($this->recursiveTransformers[$type]) ? $this->recursiveTransformers[$type] : null;
    }

    /**
     * @return Variable[]
     */
    public function getRecursiveTransformers()
    {
        return $this->recursiveTransformers;
    }

    /**
     * @param Variable[] $transformers
     */
    public function setRecursiveTransformers(array $transformers)
    {
        $this->recursiveTransformers = $transformers;
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
     * @param ExpressionMethodCallable $object
     * @param string $propertyName
     * @return FetchProperty
     */
    public function fetchProperty(ExpressionMethodCallable $object, $propertyName)
    {
        return new FetchProperty($object, $propertyName);
    }

    /**
     * @param ExpressionAssignable $var
     * @param Expression $expression
     * @return AssignVariable
     */
    public function assignVariable(ExpressionAssignable $var, Expression $expression)
    {
        return new AssignVariable($var, $expression);
    }

    /**
     * @param ExpressionAssignable $var
     * @param Expression $expression
     * @return ExpressionStatement
     */
    public function assignVariableStmt(ExpressionAssignable $var, Expression $expression)
    {
        return new ExpressionStatement($this->assignVariable($var, $expression));
    }

    /**
     * @param Expression $condition
     * @param Statement[] $success
     * @param Statement[] $failure
     * @return IfStmt
     */
    public function ifStmt(Expression $condition, array $success, array $failure = [])
    {
        return new IfStmt($condition, $success, $failure);
    }

    /**
     * @param ExpressionAssignable $var
     * @return UnsetVariable
     */
    public function unsetVariable(ExpressionAssignable $var)
    {
        return new UnsetVariable([$var]);
    }

    /**
     * @return ArrayConstructor
     */
    public function arrayConstructor()
    {
        return new ArrayConstructor();
    }

    /**
     * @param Expression $traversable
     * @param FunctionArgument $valueVar
     * @param FunctionArgument $keyVar
     * @return ForeachStmt
     */
    public function foreachStmt(Expression $traversable, FunctionArgument $valueVar, FunctionArgument $keyVar = null)
    {
        return new ForeachStmt($traversable, $valueVar, $keyVar);
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
     * @param ExpressionMethodCallable $function
     * @param Expression[] $args
     * @return FunctionCall
     */
    public function callFunction(ExpressionMethodCallable $function, array $args = [])
    {
        return new FunctionCall($function, $args);
    }

    /**
     * @param array|string|float|int|bool $value
     * @return CompileTimeValue
     */
    public function compileTimeValue($value)
    {
        if (is_resource($value) || is_object($value)) {
            throw new \InvalidArgumentException("Can't create constant from object or resource");
        }

        return new CompileTimeValue($value);
    }

    /**
     * @param ExpressionDimable $var
     * @param Expression $dim
     * @return FetchDim
     */
    public function fetchDim(ExpressionDimable $var, Expression $dim = null)
    {
        return new FetchDim($var, $dim);
    }

    /**
     * @param Expression $condition
     * @param Expression $success
     * @param Expression $failure
     * @return TernaryExpression
     */
    public function ternary(Expression $condition, Expression $success, Expression $failure)
    {
        return new TernaryExpression($condition, $success, $failure);
    }

    /**
     * @param string $value
     * @return ConstValue
     */
    public function constValue($value)
    {
        return new ConstValue($value);
    }

    /**
     * @param ClassNameConstructable $className
     * @param Expression[] $args
     * @return ConstructObject
     */
    public function constructObject(ClassNameConstructable $className, array $args = [])
    {
        return new ConstructObject($className, $args);
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
     * @param Variable[] $use
     * @return AnonymousFunction
     */
    public function anonymousFunction(array $arguments, array $statements, array $use = [])
    {
        return new AnonymousFunction($arguments, $statements, $use);
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
        return $this->anonymousFunction([
            new FunctionArgument($this->inputVariable->getName()),
            new FunctionArgument($this->transformerVariable->getName())
        ], $statements)->generate();
    }

}

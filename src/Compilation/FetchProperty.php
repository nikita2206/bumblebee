<?php

namespace Bumblebee\Compilation;

class FetchProperty
    implements Expression, ExpressionMethodCallable, AssignableExpression, ClassNameConstructable, ExpressionDimable
{

    /**
     * @var ExpressionMethodCallable
     */
    protected $object;

    /**
     * @var string
     */
    protected $propertyName;

    public function __construct(ExpressionMethodCallable $object, $propertyName)
    {
        $this->object = $object;
        $this->propertyName = $propertyName;
    }

    public function generate()
    {
        return $this->object->generate() . "->" . $this->propertyName;
    }

}

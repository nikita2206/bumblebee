<?php

namespace Bumblebee\Metadata\ObjectArray;


class ObjectArrayAccessorMetadata
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isMethod;

    /**
     * @param string $name
     * @param bool $isMethod Whether it is method name or field name
     */
    public function __construct($name, $isMethod = true)
    {
        $this->name = $name;
        $this->isMethod = $isMethod;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isMethod()
    {
        return $this->isMethod;
    }

}

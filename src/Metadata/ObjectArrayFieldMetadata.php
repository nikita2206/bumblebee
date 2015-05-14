<?php

namespace Bumblebee\Metadata;

class ObjectArrayFieldMetadata
{

    protected $type;

    protected $name;

    protected $inputName;

    protected $isMethod;

    /**
     * @param string|null $type
     * @param string $name
     * @param string $inputName
     * @param bool $isMethod
     */
    public function __construct($type, $name, $inputName, $isMethod)
    {
        $this->type = $type;
        $this->name = $name;
        $this->inputName = $inputName;
        $this->isMethod = $isMethod;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getInputName()
    {
        return $this->inputName;
    }

    /**
     * @return bool
     */
    public function isMethod()
    {
        return $this->isMethod;
    }

}

<?php

namespace Bumblebee\Metadata\ArrayToObject;

class ArrayToObjectSettingMetadata
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
     * @var ArrayToObjectArgumentMetadata[]
     */
    protected $arguments;

    /**
     * @param string $methodOrFieldName
     * @param ArrayToObjectArgumentMetadata[] $arguments
     * @param bool $isMethod
     */
    public function __construct($methodOrFieldName, array $arguments, $isMethod = true)
    {
        $this->name = $methodOrFieldName;
        $this->arguments = $arguments;
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

    /**
     * @return ArrayToObjectArgumentMetadata[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

}

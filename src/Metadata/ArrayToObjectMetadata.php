<?php

namespace Bumblebee\Metadata;

class ArrayToObjectMetadata extends TypeMetadata
{

    /**
     * @var string
     */
    protected $className;

    /**
     * @var ArrayToObjectArgumentMetadata[]
     */
    protected $constructorArguments;

    /**
     * @var ArrayToObjectSettingMetadata[]
     */
    protected $settingMetadata;

    public function __construct($className, array $constructorArguments = [], array $settingMetadata = [])
    {
        parent::__construct("array_to_object");

        $this->className = $className;
        $this->constructorArguments = $constructorArguments;
        $this->settingMetadata = $settingMetadata;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return ArrayToObjectArgumentMetadata[]
     */
    public function getConstructorArguments()
    {
        return $this->constructorArguments;
    }

    /**
     * @return ArrayToObjectSettingMetadata[]
     */
    public function getSettingMetadata()
    {
        return $this->settingMetadata;
    }

}

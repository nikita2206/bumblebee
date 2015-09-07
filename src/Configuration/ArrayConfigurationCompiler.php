<?php

namespace Bumblebee\Configuration;

use Bumblebee\Configuration\ArrayConfiguration\TransformerConfigurationCompiler;
use Bumblebee\Metadata\TypeMetadata;

class ArrayConfigurationCompiler
{

    /**
     * @var TransformerConfigurationCompiler[]
     */
    protected $compilers;

    /**
     * @var array
     */
    protected $deferred;

    /**
     * @var string
     */
    protected $currentlyCompiling;

    /**
     * @param TransformerConfigurationCompiler[] $compilers
     */
    public function __construct(array $compilers)
    {
        $this->compilers = $compilers;
    }

    /**
     * @param array $configuration
     * @return TypeMetadata[]
     * @throws \Exception
     */
    public function compile(array $configuration)
    {
        $compiled = [];

        do {
            $this->deferred = [];

            foreach ($configuration as $typeName => $typeDefinition) {
                if (!isset($typeDefinition["tran"]) && !isset($typeDefinition["transformer"])) {
                    throw new \Exception();
                }

                $transformer = isset($typeDefinition["transformer"]) ? $typeDefinition["transformer"] : $typeDefinition["tran"];

                $this->currentlyCompiling = $typeName;
                $compiled[$typeName] = $this->getCompiler($transformer)->compile($typeDefinition, $this);

                if (isset($this->deferred[$typeName])) {
                    throw new \RuntimeException();
                }
            }
        } while ($configuration = $this->deferred);

        $this->deferred = null;
        $this->currentlyCompiling = null;

        return $compiled;
    }

    public function defer($name, $tran, $props)
    {
        $name = "__" . $this->currentlyCompiling . "_" . $name;

        if (isset($this->deferred[$name])) {
            throw new \RuntimeException();
        }

        $this->deferred[$name] = $props + ["tran" => $tran];

        return $name;
    }

    /**
     * @param string $name
     * @return TransformerConfigurationCompiler
     * @throws \RuntimeException
     */
    protected function getCompiler($name)
    {
        if ( ! isset($this->compilers[$name])) {
            throw new \RuntimeException("Compiler \"{$name}\" doesn't exist");
        }

        return $this->compilers[$name];
    }

}

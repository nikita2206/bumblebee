<?php

namespace Bumblebee\Configuration;

use Bumblebee\Configuration\ArrayConfiguration\TransformerConfigurationCompiler;
use Bumblebee\Metadata\ChainMetadata;
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
     * @var array
     */
    protected $compiled;

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
        $this->compiled = [];

        do {
            $this->deferred = [];

            foreach ($configuration as $typeName => $typeDefinition) {
                if (!isset($typeDefinition["tran"]) && !isset($typeDefinition["transformer"])) {
                    throw new \Exception();
                }

                $transformer = isset($typeDefinition["transformer"]) ? $typeDefinition["transformer"] : $typeDefinition["tran"];

                $this->currentlyCompiling = $typeName;
                $this->compiled[$typeName] = $this->getCompiler($transformer)->compile($typeDefinition, $this);

                if (isset($this->deferred[$typeName])) {
                    throw new \RuntimeException();
                }
            }
        } while ($configuration = $this->deferred);

        $compiled = $this->compiled;
        $this->compiled = null;
        $this->deferred = null;
        $this->currentlyCompiling = null;

        return $compiled;
    }

    /**
     * @param string $name
     * @param string $tran
     * @param array $props
     * @return string
     */
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
     * Returns type name for chain of given types
     *
     * @param array $types
     * @return string
     */
    public function chain(array $types)
    {
        $name = "__chain_" . implode(".", $types);

        if (isset($this->compiled[$name]) || isset($this->deferred[$name])) {
            return $name;
        }
        $this->compiled[$name] = new ChainMetadata($types);

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

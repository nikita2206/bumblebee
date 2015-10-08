<?php

namespace Bumblebee\Configuration;

use Bumblebee\Configuration\ArrayConfiguration\TransformerConfigurationCompiler;
use Bumblebee\Exception\ConfigurationCompilationException;
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
     * @var TypeMetadata[]
     */
    protected $compiled;

    /**
     * @var string
     */
    protected $currentlyCompiling;

    /**
     * @var string[]
     */
    protected $deferredToRootMap;

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
     * @throws ConfigurationCompilationException
     */
    public function compile(array $configuration)
    {
        $this->compiled = [];
        $this->deferredToRootMap = [];
        $root = true;

        do {
            $this->deferred = [];

            foreach ($configuration as $typeName => $typeDefinition) {
                if (!isset($typeDefinition["tran"]) && !isset($typeDefinition["transformer"])) {
                    $erroneousType = $root ? $typeName : $this->deferredToRootMap[$typeName];
                    throw new ConfigurationCompilationException("In type '{$erroneousType}': Property 'tran' or 'transformer' is required");
                }

                $transformer = isset($typeDefinition["transformer"]) ? $typeDefinition["transformer"] : $typeDefinition["tran"];

                $this->currentlyCompiling  = $typeName;

                try {
                    $this->compiled[$typeName] = $this->getCompiler($transformer)->compile($typeDefinition, $this);
                } catch (ConfigurationCompilationException $e) {
                    $erroneousType = $root ? $typeName : $this->deferredToRootMap[$typeName];
                    throw new ConfigurationCompilationException("In type '{$erroneousType}': " . $e->getMessage(), $e);
                }

                if (isset($this->deferred[$typeName])) {
                    throw new ConfigurationCompilationException("Type '{$typeName}' was deferred for compilation" .
                        " during compilation of itself (recursive type)");
                }
            }

            $root = false;
        } while ($configuration = $this->deferred);

        $compiled = $this->compiled;
        $this->compiled = null;
        $this->deferred = null;
        $this->currentlyCompiling = null;
        $this->deferredToRootMap  = null;

        return $compiled;
    }

    /**
     * @param string $name
     * @param string $tran
     * @param array $props
     * @return string
     * @throws ConfigurationCompilationException
     */
    public function defer($name, $tran, $props)
    {
        $name  = "__" . $this->currentlyCompiling . "_" . $name;
        $props = $props + ["tran" => $tran];

        if (isset($this->deferred[$name]) && $this->deferred[$name] !== $props) {
            throw new ConfigurationCompilationException("Type '{$name}' was deferred for compilation twice");
        }

        $this->deferred[$name] = $props;
        $this->deferredToRootMap[$name] = $this->currentlyCompiling;

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
     * @throws ConfigurationCompilationException
     */
    protected function getCompiler($name)
    {
        if ( ! isset($this->compilers[$name])) {
            throw new ConfigurationCompilationException("Compiler for '{$name}' doesn't exist");
        }

        return $this->compilers[$name];
    }
}

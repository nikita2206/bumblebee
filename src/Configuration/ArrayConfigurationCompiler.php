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
     * @param array $configuration
     * @return TypeMetadata[]
     * @throws \Exception
     */
    public function compile(array $configuration)
    {
        $this->deferred = [];
        $compiled = [];

        foreach ($configuration as $typeName => $typeDefinition) {
            if ( ! isset($typeDefinition["tran"]) && ! isset($typeDefinition["transformer"])) {
                throw new \Exception();
            }

            $transformer = isset($typeDefinition["transformer"]) ? $typeDefinition["transformer"] : $typeDefinition["tran"];

            $compiled[$typeName] = $this->getCompiler($transformer)->compile($typeDefinition, $this);
        }

        $this->deferred = null;
        return $compiled;
    }

    /**
     * @param string $name
     * @return TransformerConfigurationCompiler
     * @throws \Exception
     */
    protected function getCompiler($name)
    {
        if ( ! isset($this->compilers[$name])) {
            throw new \Exception();
        }

        return $this->compilers[$name];
    }

}

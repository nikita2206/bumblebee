<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Exception\ConfigurationCompilationException;
use Bumblebee\Metadata\FunctionMetadata;

class FunctionConfigurationCompiler implements TransformerConfigurationCompiler
{
    /**
     * @inheritdoc
     */
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler)
    {
        if (!isset($configuration["func"]) || !is_string($configuration["func"])) {
            throw new ConfigurationCompilationException("Property 'func' is required to be string");
        }

        return new FunctionMetadata($configuration["func"]);
    }
}

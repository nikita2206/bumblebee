<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Exception\ConfigurationCompilationException;
use Bumblebee\Metadata\ChainMetadata;

class ChainConfigurationCompiler implements TransformerConfigurationCompiler
{
    /**
     * @inheritdoc
     */
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler)
    {
        if (!isset($configuration["chain"]) || !is_array($configuration["chain"])) {
            throw new ConfigurationCompilationException("Property 'chain' is expected to be array");
        }

        return new ChainMetadata($configuration["chain"]);
    }
}

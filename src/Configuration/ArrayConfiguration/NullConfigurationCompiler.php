<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Metadata\TypeMetadata;

/**
 * Configuration-less compiler, it can be used for transformers that don't have any configuration
 */
class NullConfigurationCompiler implements TransformerConfigurationCompiler
{
    /**
     * @inheritdoc
     */
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler)
    {
        return new TypeMetadata(isset($configuration["transformer"]) ?
            $configuration["transformer"] : $configuration["tran"]);
    }
}

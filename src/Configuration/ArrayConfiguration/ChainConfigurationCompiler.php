<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Metadata\ChainMetadata;

class ChainConfigurationCompiler implements TransformerConfigurationCompiler
{
    /**
     * @inheritdoc
     */
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler)
    {
        if (!isset($configuration["chain"])) {
            throw new \Exception("chain element is expected");
        }

        return new ChainMetadata($configuration["chain"]);
    }
}

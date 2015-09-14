<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Metadata\FunctionMetadata;

class FunctionConfigurationCompiler implements TransformerConfigurationCompiler
{
    /**
     * @inheritdoc
     */
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler)
    {
        if (!isset($configuration["func"])) {
            throw new \Exception("func option is expected");
        }

        return new FunctionMetadata($configuration["func"]);
    }
}

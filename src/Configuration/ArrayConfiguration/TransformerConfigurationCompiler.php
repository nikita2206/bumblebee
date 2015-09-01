<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Metadata\TypeMetadata;

interface TransformerConfigurationCompiler
{

    /**
     * @param array $configuration
     * @param ArrayConfigurationCompiler $compiler
     * @return TypeMetadata
     */
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler);

}

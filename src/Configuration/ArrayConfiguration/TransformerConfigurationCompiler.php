<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Exception\ConfigurationCompilationException;
use Bumblebee\Metadata\TypeMetadata;

interface TransformerConfigurationCompiler
{

    /**
     * @param array $configuration
     * @param ArrayConfigurationCompiler $compiler
     * @return TypeMetadata
     * @throws ConfigurationCompilationException
     */
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler);

}

<?php

namespace Bumblebee\Configuration;

use Bumblebee\Metadata\TypeMetadata;

interface TransformerConfigurationCompiler
{

    /**
     * @param array $configuration
     * @return TypeMetadata
     */
    public function compile(array $configuration);

}

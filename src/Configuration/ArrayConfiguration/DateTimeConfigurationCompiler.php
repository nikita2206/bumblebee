<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Metadata\DateTimeMetadata;

class DateTimeConfigurationCompiler implements TransformerConfigurationCompiler
{

    /**
     * @param array $configuration
     * @param ArrayConfigurationCompiler $compiler
     * @return DateTimeMetadata
     * @throws \Exception
     */
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler)
    {
        if (!isset($configuration["format"])) {
            throw new \Exception("format option is expected");
        }

        return new DateTimeMetadata($configuration["format"]);
    }

}

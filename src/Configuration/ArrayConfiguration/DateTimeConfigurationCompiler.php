<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Exception\ConfigurationCompilationException;
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
        if (!isset($configuration["format"]) || !is_string($configuration["format"])) {
            throw new ConfigurationCompilationException("Property 'format' is required to be string");
        }

        return new DateTimeMetadata($configuration["format"]);
    }

}

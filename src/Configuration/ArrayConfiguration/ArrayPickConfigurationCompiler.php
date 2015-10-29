<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Exception\ConfigurationCompilationException;
use Bumblebee\Metadata\ArrayPickMetadata;

class ArrayPickConfigurationCompiler implements TransformerConfigurationCompiler
{
    use ArrayConfigurationHelper;

    /**
     * @inheritdoc
     */
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler)
    {
        if ( ! isset($configuration["path"])) {
            throw new ConfigurationCompilationException("Property 'path' is required");
        }

        $path = $configuration["path"];
        if ( ! is_array($path)) {
            if (is_int($path)) {
                $path = [$path];
            } else {
                $path = $this->expandKey($path);
            }
        }

        return new ArrayPickMetadata($path, isset($configuration["default"]) ? $configuration["default"] : null);
    }
}

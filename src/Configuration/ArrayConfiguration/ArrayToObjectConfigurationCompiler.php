<?php

namespace Bumblebee\Configuration;

use Bumblebee\Metadata\TypeMetadata;

class ArrayToObjectConfigurationCompiler implements TransformerConfigurationCompiler
{

    /**
     * @param array $configuration
     * @return TypeMetadata
     * @throws \Exception
     */
    public function compile(array $configuration)
    {
        if ( ! isset($configuration["class"])) {
            throw new \Exception();
        }

        $settings = [];
        if (isset($configuration["settings"])) {
            foreach ($configuration["settings"] as $name => $arrayKey) {

            }
        }
    }

}

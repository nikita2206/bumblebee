<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata;
use Bumblebee\Metadata\ObjectArray\ObjectArrayElementMetadata;
use Bumblebee\Metadata\ObjectArray\ObjectArrayMetadata;

class ObjectArrayConfigurationCompiler implements TransformerConfigurationCompiler
{

    use ArrayConfigurationHelper;

    /**
     * @param array $configuration
     * @param ArrayConfigurationCompiler $compiler
     * @return ObjectArrayMetadata
     * @throws \Exception
     */
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler)
    {
        if (!isset($configuration["elements"]) || !is_array($configuration["elements"])) {
            throw new \Exception();
        }

        $fields = [];
        foreach ($configuration["elements"] as $elName => $elProps) {
            list($type, $accessors) = $this->extractType($elProps);
            $accessors = $this->buildAccessors($accessors);
            $fields[] = new ObjectArrayElementMetadata($type, $elName, $accessors);
        }

        return new ObjectArrayMetadata($fields);
    }

    /**
     * @param string $accessorsExpression
     * @return ObjectArrayAccessorMetadata[]
     */
    public function buildAccessors($accessorsExpression)
    {
        return array_map(function ($accessor) {
            return new ObjectArrayAccessorMetadata(
                preg_replace('!\(\)$!', "", $accessor),
                substr($accessor, -2) === "()"
            );
        }, explode("->", $accessorsExpression));
    }

}

<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Exception\ConfigurationCompilationException;
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
        $elems = isset($configuration["elements"]) ? $configuration["elements"] : [];
        if (!is_array($elems)) {
            throw new ConfigurationCompilationException("Property 'elements' is required to be an array");
        }

        $fields = [];
        foreach ($elems as $elName => $elProps) {
            list($type, $accessors) = $this->extractType($elProps);
            $accessors = $this->buildAccessors($accessors);

            if (count($type) > 1) {
                $type = [$compiler->chain($type)];
            }

            $fields[] = new ObjectArrayElementMetadata(reset($type) ?: null, $elName, $accessors);
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

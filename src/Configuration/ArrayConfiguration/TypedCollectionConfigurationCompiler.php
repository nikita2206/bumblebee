<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Exception\ConfigurationCompilationException;
use Bumblebee\Metadata\TypedCollectionMetadata;

class TypedCollectionConfigurationCompiler implements TransformerConfigurationCompiler
{
    use ArrayConfigurationHelper;

    /**
     * @inheritdoc
     */
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler)
    {
        $class = isset($configuration["class"]) ? $configuration["class"] : null;

        $keyType = $type = null;

        if (isset($configuration["keyType"])) {
            $keyType = $this->generateType($configuration["keyType"], $compiler);
        }

        if (isset($configuration["type"])) {
            $type = $this->generateType($configuration["type"], $compiler);
        }

        $keyGeneratedBy = isset($configuration["keysGenerated"]) ? $configuration["keysGenerated"] : "incrementally";
        $keysMap = [
            "incrementally" => TypedCollectionMetadata::KEY_INCREMENTING,
            "preserve"      => TypedCollectionMetadata::KEY_PRESERVE,
            "value"         => TypedCollectionMetadata::KEY_FROM_VALUE
        ];

        if ( ! isset($keysMap[$keyGeneratedBy])) {
            throw new ConfigurationCompilationException(sprintf("Property 'keysGenerated' is required to have one " .
                "of the following values: %s. %s given", implode(", ", array_keys($keysMap)), $keyGeneratedBy));
        }

        return new TypedCollectionMetadata($type, $class === null, $class, $keysMap[$keyGeneratedBy], $keyType);
    }

    protected function generateType($type, ArrayConfigurationCompiler $compiler)
    {
        list($types, $innerType) = $this->extractType($type);

        if ($innerType) {
            array_unshift($types, $innerType);
        }

        if (count($types) > 1) {
            return $compiler->chain($types);
        } else {
            return reset($types) ?: null;
        }
    }
}

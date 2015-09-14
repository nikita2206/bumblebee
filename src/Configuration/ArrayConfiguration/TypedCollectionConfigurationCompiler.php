<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
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
        $preserveKeys = isset($configuration["preserveKeys"]) && $configuration["preserveKeys"];
        $type = null;

        if (isset($configuration["type"])) {
            list($types, $innerType) = $this->extractType($configuration["type"]);

            if ($innerType) {
                array_unshift($types, $innerType);
            }

            if (count($types) > 1) {
                $type = $compiler->chain($types);
            } else {
                $type = reset($types) ?: null;
            }
        }

        return new TypedCollectionMetadata($type, $class === null, $class, $preserveKeys);
    }
}

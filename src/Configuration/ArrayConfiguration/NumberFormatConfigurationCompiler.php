<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Metadata\NumberFormatMetadata;

class NumberFormatConfigurationCompiler implements TransformerConfigurationCompiler
{
    /**
     * @inheritdoc
     */
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler)
    {
        $decimals = isset($configuration["decs"]) ? $configuration["decs"] : 0;
        $decPoint = isset($configuration["decPoint"]) ? $configuration["decPoint"] : ".";
        $thousandSep = isset($configuration["sep"]) ? $configuration["sep"] : ",";

        return new NumberFormatMetadata($decimals, $decPoint, $thousandSep);
    }
}

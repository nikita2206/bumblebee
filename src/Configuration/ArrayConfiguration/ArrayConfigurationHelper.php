<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

trait ArrayConfigurationHelper
{
    /**
     * @param string $value
     * @return array(string[] $type, string $remainingKey)
     */
    protected function extractType($value)
    {
        $value = trim($value);
        $typeChain = [];

        while (preg_match('!^([\w-]+?)\\((.*)\\)$!', $value, $match)) {
            $typeChain[] = $match[1];
            $value = $match[2];
        }

        return [array_reverse($typeChain), $value];
    }
}

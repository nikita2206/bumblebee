<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Exception\ConfigurationCompilationException;

trait ArrayConfigurationHelper
{
    /**
     * @param string $value
     * @return array(string[] $type, string $remainingKey)
     * @throws ConfigurationCompilationException
     */
    protected function extractType($value)
    {
        $value = trim($value);
        $typeChain = [];

        while (preg_match('!^([\w-]+?)\\((.+)\\)$!', $value, $match)) {
            if (substr($match[2], -1) === "(") {
                break;
            }

            $typeChain[] = $match[1];
            $value = $match[2];
        }

        return [array_reverse($typeChain), $value];
    }
}

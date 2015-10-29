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
            if (substr(rtrim($match[2]), -1) === "(") {
                break;
            }

            $typeChain[] = $match[1];
            $value = $match[2];
        }

        return [array_reverse($typeChain), trim($value)];
    }

    /**
     * Expands string key from format "foo[bar][poo]" to array("foo", "bar", "poo")
     *
     * @param string $key
     * @return array
     * @throws \Exception
     */
    protected function expandKey($key)
    {
        $key = trim($key);
        $pos = strpos($key, "[");

        if ($pos === false) {
            return [$key];
        } else {
            $expanded = [substr($key, 0, $pos)];
        }

        if ($pos + 1 === strlen($key)) {
            throw new ConfigurationCompilationException('Expected "]", got end of the string');
        }

        while ($pos + 1 < strlen($key)) {
            if ($key[$pos] === "[") {
                $newPos = strpos($key, "]", $pos);

                if ($newPos === false) {
                    throw new ConfigurationCompilationException('Expected "]", got end of the string');
                } else {
                    if ($pos + 1 === $newPos) {
                        throw new ConfigurationCompilationException("Expected non-empty string literal, got \"\"");
                    }

                    $expanded[] = substr($key, $pos + 1, $newPos - $pos - 1);
                    $pos = $newPos;
                }
            } elseif ($key[$pos] === "]") {
                $pos++;

                if ($key[$pos] !== "[") {
                    throw new ConfigurationCompilationException("Expected \"[\", got \"{$key[$pos]}\"");
                }
            }
        }

        return $expanded;
    }
}

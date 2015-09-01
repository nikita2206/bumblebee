<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler as Compiler;
use Bumblebee\Metadata\ArrayToObject\ArrayToObjectArgumentMetadata;
use Bumblebee\Metadata\ArrayToObject\ArrayToObjectSettingMetadata;
use Bumblebee\Metadata\TypeMetadata;

class ArrayToObjectConfigurationCompiler implements TransformerConfigurationCompiler
{

    /**
     * @param array $configuration
     * @param Compiler $compiler
     * @return TypeMetadata
     * @throws \Exception
     */
    public function compile(array $configuration, Compiler $compiler)
    {
        if ( ! isset($configuration["class"]) || ! is_string($configuration["class"])) {
            throw new \Exception();
        }

        $settings = [];
        if (isset($configuration["settings"])) {
            foreach ($configuration["settings"] as $name => $properties) {
                if (substr($name, -2) === "()") {
                    $settings[] = $this->compileMethodArgs(substr($name, 0, -2), $properties);
                } else {
                    $settings[] = $this->compileFieldAssignment($name, $properties);
                }
            }
        }
    }
    
    protected function compileMethodArgs($methodName, $properties, Compiler $compiler)
    {
        
    }
    
    protected function compileFieldAssignment($name, $properties, Compiler $compiler)
    {
        $type = null;
        $key = null;

        if (is_array($properties)) {
            if (isset($properties["key"])) {
                if (is_array($properties["key"])) {
                    $key = $properties["key"];
                } else {
                    list($type, $key) = $this->extractType($properties["key"]);
                }

                if (isset($properties["type"]) && $type !== null) {
                    throw new \Exception();
                } else {
                    $type = $properties["type"];
                }
            }
        } else {
            list($type, $key) = $this->extractType($properties);
            $key = $this->expandKey($key);
        }

        return new ArrayToObjectSettingMetadata($name, [
            new ArrayToObjectArgumentMetadata($type, $key)
        ], false);
    }

    /**
     * @param string $value
     * @return array(null|string $type, string $remainingKey)
     */
    protected function extractType($value)
    {
        if (preg_match('!^(\w+?)\\((.+)\\)$!', trim($value), $match)) {
            return [$match[1], $match[2]];
        }

        return [null, $value];
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

        while ($pos + 1 < strlen($key)) {
            if ($key[$pos] === "[") {
                $newPos = strpos($key, "]", $pos);

                if ($newPos === false) {
                    throw new \Exception('Expected "]", got end of the string');
                } else {
                    $expanded[] = substr($key, $pos + 1, $newPos - $pos - 1);
                    $pos = $newPos;
                }
            } elseif ($key[$pos] === "]") {
                $pos++;

                if ($key[$pos] !== "[") {
                    throw new \Exception("Expected \"[\", got \"{$key[$pos]}\"");
                }
            }
        }

        return $expanded;
    }

}

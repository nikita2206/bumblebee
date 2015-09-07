<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler as Compiler;
use Bumblebee\Metadata\ArrayToObject\ArrayToObjectArgumentMetadata;
use Bumblebee\Metadata\ArrayToObject\ArrayToObjectMetadata;
use Bumblebee\Metadata\ArrayToObject\ArrayToObjectSettingMetadata;
use Bumblebee\Metadata\TypeMetadata;

class ArrayToObjectConfigurationCompiler implements TransformerConfigurationCompiler
{

    use ArrayConfigurationHelper;

    /**
     * @param array $configuration
     * @param Compiler $compiler
     * @return TypeMetadata
     * @throws \Exception
     */
    public function compile(array $configuration, Compiler $compiler)
    {
        if ( ! isset($configuration["class"]) || ! is_string($configuration["class"])) {
            throw new \Exception("class attribute is required");
        }

        $ctorArgs = [];
        $settings = [];
        if (isset($configuration["settings"])) {
            foreach ($configuration["settings"] as $name => $properties) {
                if (substr($name, -2) === "()") {
                    $settings[] = $this->compileSetting(substr($name, 0, -2), $properties, true, $compiler);
                } else {
                    $settings[] = $this->compileSetting($name, $properties, false, $compiler);
                }
            }
        }
        if (isset($configuration["constructor"])) {
            foreach ($configuration["constructor"] as $arg) {
                $ctorArgs[] = $this->compileArg("ctor", $arg, $compiler);
            }
        }

        return new ArrayToObjectMetadata($configuration["class"], $ctorArgs, $settings);
    }

    protected function compileSetting($name, $properties, $isMethod, Compiler $compiler)
    {
        $args = [];
        if ($isMethod) {
            foreach ($properties as $props) {
                $args[] = $this->compileArg($name, $props, $compiler);
            }
        } else {
            $args[] = $this->compileArg($name, $properties, $compiler);
        }

        return new ArrayToObjectSettingMetadata($name, $args, $isMethod);
    }

    protected function compileArg($name, $properties, Compiler $compiler)
    {
        $type = null;
        $key = null;
        $assumeAlwaysSet = true;
        $fallbackValue = null;

        if (is_array($properties)) {
            if (!isset($properties[0])) {
                if (!isset($properties["key"])) {
                    $key = [];
                } elseif (is_array($properties["key"])) {
                    $key = $properties["key"];
                } else {
                    list($type, $key) = $this->extractType($properties["key"]);
                    $key = $this->expandKey($key);
                }

                if (isset($properties["type"]) && $type !== null) {
                    throw new \Exception("Type is set twice");
                } else {
                    $type = $properties["type"];
                }

                if (isset($properties["check_isset"])) {
                    $assumeAlwaysSet = !$properties["check_isset"];
                }

                if ( ! $assumeAlwaysSet && isset($properties["fallback"])) {
                    $fallbackValue = $properties["fallback"];
                }

                if (isset($properties["tran"])) {
                    $type = $compiler->defer($name . "_" . implode(".", $key), $properties["tran"],
                        isset($properties["props"]) ? $properties["props"] : []);
                }
            } else {
                $key = $properties;
            }
        } else {
            if ($properties[0] === "?") {
                $properties = substr($properties, 1);
                $assumeAlwaysSet = false;
            }

            list($type, $key) = $this->extractType($properties);
            $key = $this->expandKey($key);
        }

        return new ArrayToObjectArgumentMetadata($type, $key, $assumeAlwaysSet, $fallbackValue);
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

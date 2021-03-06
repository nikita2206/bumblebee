<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfigurationCompiler as Compiler;
use Bumblebee\Exception\ConfigurationCompilationException;
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
            throw new ConfigurationCompilationException("Property 'class' is required to be string");
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
            foreach ($configuration["constructor"] as $pos => $arg) {
                $ctorArgs[] = $this->compileArg("__construct", $pos, $arg, $compiler);
            }
        }

        return new ArrayToObjectMetadata($configuration["class"], $ctorArgs, $settings);
    }

    protected function compileSetting($name, $properties, $isMethod, Compiler $compiler)
    {
        $args = [];
        if ($isMethod) {
            foreach ($properties as $pos => $props) {
                try {

                    $args[] = $this->compileArg($name, $pos, $props, $compiler);

                } catch (ConfigurationCompilationException $e) {
                    throw new ConfigurationCompilationException($e->getMessage() . " in ->{$name}(), " .
                        "argument #{$pos}", $e);
                }
            }
        } else {
            try {

                $args[] = $this->compileArg($name, 0, $properties, $compiler);

            } catch (ConfigurationCompilationException $e) {
                throw new ConfigurationCompilationException($e->getMessage() . " in ->{$name}", $e);
            }
        }

        return new ArrayToObjectSettingMetadata($name, $args, $isMethod);
    }

    protected function compileArg($methName, $argPos, $properties, Compiler $compiler)
    {
        $type = [];
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

                if (isset($properties["type"]) && $type !== null && $type !== []) {
                    throw new ConfigurationCompilationException("Property 'type' is declared twice: " .
                        "first time in {$properties["key"]}, second time in 'type' attribute");
                } elseif (isset($properties["type"])) {
                    $type = (array)$properties["type"];
                }

                if (isset($properties["check_isset"])) {
                    $assumeAlwaysSet = !$properties["check_isset"];
                }

                if ( ! $assumeAlwaysSet && isset($properties["fallback"])) {
                    $fallbackValue = $properties["fallback"];
                }

                if (isset($properties["tran"])) {
                    $type = [$compiler->defer("{$methName}_{$argPos}_" . implode(".", $key), $properties["tran"],
                        isset($properties["props"]) ? $properties["props"] : [])];
                }
            } else {
                $key = $properties;
            }
        } else {
            list($type, $key) = $this->extractType($properties);
            if ($key[0] === "?") {
                $key = substr($key, 1);
                $assumeAlwaysSet = false;
            }

            $key = $this->expandKey($key);
        }

        if (count($type) > 1) {
            $type = $compiler->chain($type);
        } else {
            $type = reset($type) ?: null;
        }

        return new ArrayToObjectArgumentMetadata($type, $key, $assumeAlwaysSet, $fallbackValue);
    }
}

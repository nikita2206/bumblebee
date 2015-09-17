<?php

namespace Bumblebee\TypeTransformer;

use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Compilation\CompilationFrame;
use Bumblebee\Compilation\Expression;
use Bumblebee\Compilation\ExpressionDimable;
use Bumblebee\Compiler;
use Bumblebee\Metadata\ArrayToObject\ArrayToObjectArgumentMetadata;
use Bumblebee\Metadata\ArrayToObject\ArrayToObjectMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;
use Bumblebee\TransformerInterface;

class ArrayToObjectTransformer implements CompilableTypeTransformer
{
    /**
     * @inheritdoc
     */
    public function transform($data, TypeMetadata $metadata, TransformerInterface $transformer)
    {
        if ( ! $metadata instanceof ArrayToObjectMetadata) {
            throw new \InvalidArgumentException();
        }

        if ( ! is_array($data) && ! $data instanceof \ArrayAccess) {
            throw new \RuntimeException();
        }

        $className = $metadata->getClassName();

        if ($metadata->getConstructorArguments()) {
            $reflection = new \ReflectionClass($className);
            $object = $reflection->newInstanceArgs($this->fetchArguments($data, $metadata->getConstructorArguments(), $transformer));
        } else {
            $object = new $className();
        }

        foreach ($metadata->getSettingMetadata() as $setting) {
            if ($setting->isMethod()) {
                call_user_func_array([$object, $setting->getName()], $this->fetchArguments($data, $setting->getArguments(), $transformer));
            } else {
                $object->{$setting->getName()} = $this->fetchArguments($data, $setting->getArguments(), $transformer)[0];
            }
        }

        return $object;
    }

    /**
     * @param array|\ArrayAccess $data
     * @param ArrayToObjectArgumentMetadata[] $argsMetadata
     * @param TransformerInterface $transformer
     * @return array
     */
    protected function fetchArguments($data, $argsMetadata, TransformerInterface $transformer)
    {
        $args = [];

        foreach ($argsMetadata as $arg) {
            $argVal = $data;
            if ($arg->isKeyAlwaysSet()) {
                foreach ($arg->getArrayKey() as $key) {
                    $argVal = $argVal[$key];
                }
            } else {
                foreach ($arg->getArrayKey() as $key) {
                    if (isset($data[$key])) {
                        $argVal = $data[$key];
                    } else {
                        $argVal = $arg->getFallbackData();
                        break;
                    }
                }
            }

            if ($arg->getType()) {
                $argVal = $transformer->transform($argVal, $arg->getType());
            }

            $args[] = $argVal;
        }

        return $args;
    }

    /**
     * @inheritdoc
     */
    public function validateMetadata(ValidationContext $context, TypeMetadata $metadata)
    {
        if ( ! $metadata instanceof ArrayToObjectMetadata) {
            return [new ValidationError(sprintf("%s expects instance of ArrayToObjectMetadata, %s given", __CLASS__, get_class($metadata)))];
        }

        $errors = $this->validateArgs("__construct", $metadata->getConstructorArguments(), $context);

        foreach ($metadata->getSettingMetadata() as $idx => $setting) {
            $errors = array_merge($errors, $this->validateArgs($setting->getName(), $setting->getArguments(), $context));

            if ( ! $setting->isMethod() && count($setting->getArguments()) > 1) {
                $errors[] = new ValidationError(sprintf("Property assigning expects only one argument, %d given for property %s",
                    count($setting->getArguments()), $setting->getName()));
            }
        }

        return $errors;
    }

    /**
     * @param string $methodName
     * @param ArrayToObjectArgumentMetadata[] $argsMetadata
     * @param ValidationContext $context
     * @return ValidationError[]
     */
    protected function validateArgs($methodName, $argsMetadata, ValidationContext $context)
    {
        $errors = [];

        foreach ($argsMetadata as $idx => $arg) {
            if ($arg->getType()) {
                $context->validateLater($arg->getType(), "{$context->getCurrentlyValidatingType()} -> {$methodName} -> Arg#{$idx}");
            }

            if (is_resource($arg->getFallbackData()) || is_object($arg->getFallbackData())) {
                $arrayKey = $arg->getArrayKey();
                $firstKey = (string)array_shift($arrayKey);
                $arrayKeyString = array_reduce($arrayKey, function ($composed, $key) {
                    return $composed . "[{$key}]";
                }, $firstKey);
                $errors[] = new ValidationError("{$methodName} argument#{$idx} (arrayKey={$arrayKeyString}) can't have fallback of type resource or object");
            }
        }

        return $errors;
    }

    /**
     * @inheritdoc
     */
    public function compile(CompilationContext $ctx, TypeMetadata $metadata, Compiler $compiler)
    {
        if ( ! $metadata instanceof ArrayToObjectMetadata) {
            throw new \InvalidArgumentException();
        }

        $frame = $ctx->getCurrentFrame();
        $input = $frame->getInputData();

        if ( ! ($input instanceof ExpressionDimable)) {
            $nonDimable = $input;
            $input      = $ctx->createFreeVariable();
            $frame->addStatement($ctx->assignVariable($input, $nonDimable));
        }

        $args = $this->compileArguments($input, $metadata->getConstructorArguments(), $ctx, $compiler);
        $objectVal = $ctx->constructObject($ctx->constValue($metadata->getClassName()), $args);

        if ($metadata->getSettingMetadata()) {
            $objectVar = $ctx->createFreeVariable();
            $frame->addStatement($ctx->assignVariable($objectVar, $objectVal));

            foreach ($metadata->getSettingMetadata() as $setting) {
                $args = $this->compileArguments($input, $setting->getArguments(), $ctx, $compiler);

                if ($setting->isMethod()) {
                    $frame->addStatement($ctx->callMethod($objectVar, $setting->getName(), $args));
                } else {
                    $frame->addStatement($ctx->assignVariable($ctx->fetchProperty($objectVar, $setting->getName()), $args[0]));
                }
            }

            $frame->setResult($objectVar);
        } else {
            $frame->setResult($objectVal);
        }
    }

    /**
     * @param ExpressionDimable $input
     * @param ArrayToObjectArgumentMetadata[] $argsMetadata
     * @param CompilationContext $ctx
     * @param Compiler $compiler
     * @return Expression[]
     */
    protected function compileArguments(ExpressionDimable $input, $argsMetadata, CompilationContext $ctx, Compiler $compiler)
    {
        $frame = $ctx->getCurrentFrame();
        $args = [];
        foreach ($argsMetadata as $arg) {
            $fetchExp = $input;
            foreach ($arg->getArrayKey() as $key) {
                $fetchExp = $ctx->fetchDim($fetchExp, $ctx->compileTimeValue($key));
            }

            if ($arg->getType()) {
                $ctx->pushFrame(new CompilationFrame($fetchExp, $arg->getType()));
                $compiler->_compileType($ctx, $arg->getType());
                $argFrame = $ctx->popFrame();

                if ($argFrame->getStatements() && ! $arg->isKeyAlwaysSet()) {
                    $frame->addStatement($ctx->ifStmt($ctx->callFunction($ctx->constValue("isset"), [$fetchExp]), $argFrame->getStatements()));
                } else {
                    $frame->addStatements($argFrame->getStatements());
                }

                $argVal = $argFrame->getResult();
            } else {
                $argVal = $fetchExp;
            }

            if ( ! $arg->isKeyAlwaysSet()) {
                // wrap it in isset($a) ? $a : fallback
                $argVal = $ctx->ternary($ctx->callFunction($ctx->constValue("isset"), [$fetchExp]),
                    $argVal,
                    $ctx->compileTimeValue($arg->getFallbackData()));
            }

            $args[] = $argVal;
        }

        return $args;
    }
}

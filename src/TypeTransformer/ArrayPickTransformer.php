<?php

namespace Bumblebee\TypeTransformer;

use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Compilation\ExpressionDimable;
use Bumblebee\Compiler;
use Bumblebee\Metadata\ArrayPickMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;
use Bumblebee\TransformerInterface;

class ArrayPickTransformer implements CompilableTypeTransformer
{
    /**
     * @inheritdoc
     */
    public function transform($data, TypeMetadata $metadata, TransformerInterface $transformer)
    {
        if ( ! $metadata instanceof ArrayPickMetadata) {
            throw new \InvalidArgumentException();
        }

        foreach ($metadata->getPath() as $key) {
            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                $data = $metadata->getDefault();
                break;
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function compile(CompilationContext $ctx, TypeMetadata $metadata, Compiler $compiler)
    {
        if ( ! $metadata instanceof ArrayPickMetadata) {
            throw new \InvalidArgumentException();
        }

        $input = $ctx->getCurrentFrame()->getInputData();
        if ( ! $input instanceof ExpressionDimable) {
            $nonDimable = $input;
            $input = $ctx->createFreeVariable();
            $ctx->getCurrentFrame()->addStatement($ctx->assignVariable($input, $nonDimable));
        }

        $result = $input;
        foreach ($metadata->getPath() as $key) {
            $result = $ctx->fetchDim($result, $ctx->compileTimeValue($key));
        }

        $result = $ctx->ternary($ctx->callFunction($ctx->constValue("isset"), [$result]), $result, $ctx->compileTimeValue($metadata->getDefault()));
        $ctx->getCurrentFrame()->setResult($result);
    }

    /**
     * @inheritdoc
     */
    public function validateMetadata(ValidationContext $context, TypeMetadata $metadata)
    {
        if ( ! $metadata instanceof ArrayPickMetadata) {
            return [new ValidationError(sprintf("%s expects instance of ArrayPickMetadata, %s given", __CLASS__, get_class($metadata)))];
        }

        $errors = [];
        foreach ($metadata->getPath() as $key) {
            if ( ! is_string($key) && ! is_int($key)) {
                $errors[] = new ValidationError(sprintf("path should consist of array keys, can't use %s type as a key", gettype($key)));
            }
        }

        if (is_resource($metadata->getDefault()) || is_object($metadata->getDefault())) {
            $errors[] = new ValidationError("default value can not be neither object nor resource");
        }

        return $errors;
    }
}

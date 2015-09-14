<?php

namespace Bumblebee\TypeTransformer;

use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Compiler;
use Bumblebee\Metadata\FunctionMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;
use Bumblebee\Transformer;

class FunctionTransformer implements CompilableTypeTransformer
{
    /**
     * @inheritdoc
     */
    public function transform($data, TypeMetadata $metadata, Transformer $transformer)
    {
        if ( ! $metadata instanceof FunctionMetadata) {
            throw new \InvalidArgumentException();
        }

        $func = $metadata->getFunction();
        return $func($data);
    }

    /**
     * @inheritdoc
     */
    public function validateMetadata(ValidationContext $context, TypeMetadata $metadata)
    {
        if ( ! $metadata instanceof FunctionMetadata) {
            return [new ValidationError(sprintf("%s expects instance of FunctionMetadata, %s given", __CLASS__, get_class($metadata)))];
        }

        if ( ! function_exists($metadata->getFunction())) {
            return [new ValidationError(sprintf("Function '%s' doesn't exist", $metadata->getFunction()))];
        }
        return [];
    }

    /**
     * @inheritdoc
     */
    public function compile(CompilationContext $ctx, TypeMetadata $metadata, Compiler $compiler)
    {
        if ( ! $metadata instanceof FunctionMetadata) {
            throw new \InvalidArgumentException();
        }

        $funcCall = $ctx->callFunction($ctx->constValue($metadata->getFunction()), [
            $ctx->getCurrentFrame()->getInputData()
        ]);
        $ctx->getCurrentFrame()->setResult($funcCall);
    }
}

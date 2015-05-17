<?php

namespace Bumblebee\TypeTransformer;

use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Compiler;
use Bumblebee\Metadata\NumberFormatMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;
use Bumblebee\Transformer;

class NumberFormatTransformer implements CompilableTypeTransformer
{
    /**
     * @param mixed $data
     * @param TypeMetadata $metadata
     * @param Transformer $transformer
     * @return mixed
     */
    public function transform($data, TypeMetadata $metadata, Transformer $transformer)
    {
        if ( ! $metadata instanceof NumberFormatMetadata) {
            throw new \InvalidArgumentException();
        }

        return number_format($data, $metadata->getDecimals(), $metadata->getDecPoint(), $metadata->getThousandsSep());
    }

    /**
     * @param ValidationContext $context
     * @param TypeMetadata $metadata
     * @return ValidationError[]
     */
    public function validateMetadata(ValidationContext $context, TypeMetadata $metadata)
    {
        if ( ! $metadata instanceof NumberFormatMetadata) {
            return [new ValidationError(sprintf("%s expects instance of NumberFormatMetadata, %s given", __CLASS__, get_class($metadata)))];
        }

        return [];
    }

    /**
     * @param CompilationContext $ctx
     * @param TypeMetadata $metadata
     * @param Compiler $compiler
     */
    public function compile(CompilationContext $ctx, TypeMetadata $metadata, Compiler $compiler)
    {
        if ( ! $metadata instanceof NumberFormatMetadata) {
            throw new \InvalidArgumentException();
        }

        $ctx->getCurrentFrame()->setResult($ctx->callFunction($ctx->constValue("number_format"), [
            $ctx->getCurrentFrame()->getInputData(),
            $ctx->compileTimeValue($metadata->getDecimals()),
            $ctx->compileTimeValue($metadata->getDecPoint()),
            $ctx->compileTimeValue($metadata->getThousandsSep())
        ]));
    }
}

<?php

namespace Bumblebee\TypeTransformer;

use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Compilation\ExpressionMethodCallable;
use Bumblebee\Compilation\Variable;
use Bumblebee\Compiler;
use Bumblebee\Metadata\DateTimeMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;
use Bumblebee\Transformer;

class DateTimeTextTransformer implements CompilableTypeTransformer
{
    /**
     * @param mixed $data
     * @param TypeMetadata $metadata
     * @param Transformer $transformer
     * @return mixed
     */
    public function transform($data, TypeMetadata $metadata, Transformer $transformer)
    {
        if ( ! $data instanceof \DateTime && class_exists('DateTimeImmutable', false) && ! $data instanceof \DateTimeImmutable) {
            throw new \RuntimeException();
        }

        if ( ! $metadata instanceof DateTimeMetadata) {
            throw new \InvalidArgumentException();
        }

        return $data->format($metadata->getFormat());
    }

    /**
     * @param ValidationContext $context
     * @param TypeMetadata $metadata
     * @return ValidationError[]
     */
    public function validateMetadata(ValidationContext $context, TypeMetadata $metadata)
    {
        if ( ! $metadata instanceof DateTimeMetadata) {
            return [new ValidationError(sprintf("%s expects instance of DateTimeMetadata, %s given", __CLASS__, get_class($metadata)))];
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
        if ( ! $metadata instanceof DateTimeMetadata) {
            throw new \InvalidArgumentException();
        }

        $inputData = $ctx->getCurrentFrame()->getInputData();
        if ( ! $inputData instanceof ExpressionMethodCallable) {
            $frame = $ctx->getCurrentFrame();
            $nonCallableInput = $inputData;
            $inputData = $ctx->createFreeVariable();
            $result = $ctx->createFreeVariable();
            $frame->addStatement($ctx->assignVariableStmt($inputData, $nonCallableInput));
            $frame->addStatement($ctx->assignVariableStmt($result,
                $ctx->callMethod($inputData, "format", [$ctx->compileTimeValue($metadata->getFormat())])));
            $frame->addStatement($ctx->unsetVariable($inputData));
        } else {
            $result = $ctx->callMethod($inputData, "format", [$ctx->compileTimeValue($metadata->getFormat())]);
        }

        $ctx->getCurrentFrame()->setResult($result);
    }
}

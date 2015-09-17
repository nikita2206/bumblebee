<?php

namespace Bumblebee\TypeTransformer;

use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Compilation\CompilationFrame;
use Bumblebee\Compiler;
use Bumblebee\Metadata\ChainMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;
use Bumblebee\TransformerInterface;

class ChainTransformer implements CompilableTypeTransformer
{
    /**
     * @inheritdoc
     */
    public function transform($data, TypeMetadata $metadata, TransformerInterface $transformer)
    {
        if ( ! $metadata instanceof ChainMetadata) {
            throw new \InvalidArgumentException();
        }

        foreach ($metadata->getChain() as $type) {
            $data = $transformer->transform($data, $type);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function validateMetadata(ValidationContext $context, TypeMetadata $metadata)
    {
        if ( ! $metadata instanceof ChainMetadata) {
            return [new ValidationError(sprintf("%s expects instance of ChainMetadata, %s given", __CLASS__, get_class($metadata)))];
        }

        if ( ! is_array($metadata->getChain())) {
            return [new ValidationError(sprintf("Chain is expected to be an array of transformer types, %s given", gettype($metadata->getChain())))];
        }

        $errors = [];
        foreach ($metadata->getChain() as $type) {
            if ( ! is_string($type)) {
                $errors[] = new ValidationError(sprintf("Transformer type is a string, %s given", gettype($type)));
            } else {
                $context->validateLater($type, $context->getCurrentlyValidatingType());
            }
        }

        return $errors;
    }

    /**
     * @inheritdoc
     */
    public function compile(CompilationContext $ctx, TypeMetadata $metadata, Compiler $compiler)
    {
        if ( ! $metadata instanceof ChainMetadata) {
            throw new \InvalidArgumentException();
        }

        $frame = $ctx->getCurrentFrame();
        $input = $frame->getInputData();

        foreach ($metadata->getChain() as $typeName) {
            $ctx->pushFrame(new CompilationFrame($input, $typeName));
            $compiler->_compileType($ctx, $typeName);
            $typeFrame = $ctx->popFrame();

            $frame->addStatements($typeFrame->getStatements());
            $input = $typeFrame->getResult();
        }

        $frame->setResult($input);
    }
}

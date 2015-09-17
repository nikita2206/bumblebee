<?php

namespace Bumblebee\TypeTransformer;


use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Compilation\CompilationFrame;
use Bumblebee\Compilation\FunctionArgument;
use Bumblebee\Compiler;
use Bumblebee\Metadata\TypedCollectionMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;
use Bumblebee\TransformerInterface;

class TypedCollectionTransformer implements CompilableTypeTransformer
{
    /**
     * @inheritdoc
     */
    public function transform($data, TypeMetadata $metadata, TransformerInterface $transformer)
    {
        if ( ! $metadata instanceof TypedCollectionMetadata) {
            throw new \InvalidArgumentException();
        }

        if ( ! $data instanceof \Traversable && ! is_array($data)) {
            throw new \RuntimeException();
        }

        $className = $metadata->getCollectionClassName();
        $col = $metadata->shouldTransformIntoArray() ? [] : new $className;

        if ($metadata->shouldPreserveKeys()) {
            foreach ($data as $k => $v) {
                $col[$k] = $metadata->getChildrenType() ? $transformer->transform($v, $metadata->getChildrenType()) : $v;
            }
        } else {
            foreach ($data as $v) {
                $col[] = $metadata->getChildrenType() ? $transformer->transform($v, $metadata->getChildrenType()) : $v;
            }
        }

        return $col;
    }

    /**
     * @inheritdoc
     */
    public function validateMetadata(ValidationContext $context, TypeMetadata $metadata)
    {
        if ( ! $metadata instanceof TypedCollectionMetadata) {
            return [new ValidationError(sprintf("%s expects TypedCollectionMetadata, %s given", __CLASS__, get_class($metadata)))];
        }

        if ($metadata->getChildrenType()) {
            $context->validateLater($metadata->getChildrenType(), $context->getCurrentlyValidatingType());
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function compile(CompilationContext $ctx, TypeMetadata $metadata, Compiler $compiler)
    {
        if ( ! $metadata instanceof TypedCollectionMetadata) {
            throw new \InvalidArgumentException();
        }

        $frame = $ctx->getCurrentFrame();
        $colVar = $ctx->createFreeVariable("{$frame->getType()}_col");
        if ($metadata->shouldTransformIntoArray()) {
            $colVal = $ctx->arrayConstructor();
        } else {
            $colVal = $ctx->constructObject($ctx->constValue($metadata->getCollectionClassName()));
        }
        $frame->addStatement($ctx->assignVariable($colVar, $colVal));

        $keyVar = null;
        if ($metadata->shouldPreserveKeys()) {
            $keyVar = $ctx->createFreeVariable("{$frame->getType()}_key");
        }
        $valVar = $ctx->createFreeVariable("{$frame->getType()}_val");

        $foreach = $ctx->foreachStmt($frame->getInputData(),
            new FunctionArgument($valVar->getName()), $keyVar ? new FunctionArgument($keyVar->getName()) : null);

        if ($metadata->getChildrenType()) {
            $ctx->pushFrame(new CompilationFrame($valVar, $metadata->getChildrenType()));
            $compiler->_compileType($ctx, $metadata->getChildrenType());
            $childFrame = $ctx->popFrame();
            $valVar = $childFrame->getResult();

            foreach ($childFrame->getStatements() as $stmt) {
                $foreach->addStatement($stmt);
            }
        }

        $foreach->addStatement($ctx->assignVariableStmt($ctx->fetchDim($colVar, $keyVar), $valVar));

        $frame->addStatement($foreach);
        $frame->setResult($colVar);
    }

}

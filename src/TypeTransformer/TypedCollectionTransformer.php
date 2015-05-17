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
use Bumblebee\Transformer;

class TypedCollectionTransformer implements CompilableTypeTransformer
{
    /**
     * @param mixed $data
     * @param TypeMetadata $metadata
     * @param Transformer $transformer
     * @return mixed
     */
    public function transform($data, TypeMetadata $metadata, Transformer $transformer)
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
     * @param ValidationContext $context
     * @param TypeMetadata $metadata
     * @return ValidationError[]
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
     * @param CompilationContext $ctx
     * @param TypeMetadata $metadata
     * @param Compiler $compiler
     */
    public function compile(CompilationContext $ctx, TypeMetadata $metadata, Compiler $compiler)
    {
        if ( ! $metadata instanceof TypedCollectionMetadata) {
            throw new \InvalidArgumentException();
        }

        $frame = $ctx->getCurrentFrame();
        $safeTypeName = preg_replace("!^[0-9]+([_A-z][_A-z0-9]+)?.*$!", '\1', $frame->getType());
        $colVar = $ctx->createFreeVariable("{$safeTypeName}_col");
        if ($metadata->shouldTransformIntoArray()) {
            $colVal = $ctx->arrayConstructor();
        } else {
            $colVal = $ctx->constructObject($ctx->constValue($metadata->getCollectionClassName()));
        }
        $frame->addStatement($ctx->assignVariable($colVar, $colVal));

        $keyVar = null;
        if ($metadata->shouldPreserveKeys()) {
            $keyVar = $ctx->createFreeVariable("{$safeTypeName}_key");
        }
        $valVar = $ctx->createFreeVariable("{$safeTypeName}_val");

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

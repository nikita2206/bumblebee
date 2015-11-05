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

        if ($metadata->getKeyType()) {
            $keyType = $metadata->getKeyType();
            $applyKeyType = function ($k) use ($transformer, $keyType) {
                return $transformer->transform($k, $keyType);
            };
        } else {
            $applyKeyType = function ($k) { return $k; };
        }

        if ($metadata->keysGeneratedBy() === TypedCollectionMetadata::KEY_PRESERVE) {
            foreach ($data as $k => $v) {
                $col[$applyKeyType($k)] = $metadata->getChildrenType() ? $transformer->transform($v, $metadata->getChildrenType()) : $v;
            }
        } elseif ($metadata->keysGeneratedBy() === TypedCollectionMetadata::KEY_FROM_VALUE) {
            foreach ($data as $k => $v) {
                $col[$applyKeyType($v)] = $metadata->getChildrenType() ? $transformer->transform($v, $metadata->getChildrenType()) : $v;
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

        $errors = [];
        if ($metadata->getKeyType()) {
            $context->validateLater($metadata->getKeyType(), $context->getCurrentlyValidatingType());

            if ($metadata->keysGeneratedBy() === TypedCollectionMetadata::KEY_INCREMENTING) {
                $errors[] = new ValidationError("'keyType' is not applied with 'keysGeneratedBy' = KEY_INCREMENTING");
            }
        }

        $keyGenerationTypes = [TypedCollectionMetadata::KEY_INCREMENTING, TypedCollectionMetadata::KEY_FROM_VALUE,
            TypedCollectionMetadata::KEY_PRESERVE];
        if ( ! in_array($metadata->keysGeneratedBy(), $keyGenerationTypes, true)) {
            $errors[] = new ValidationError("'keysGeneratedBy' has unexpected value '{$metadata->keysGeneratedBy()}'");
        }

        return $errors;
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

        $resultValVar = $valVar = $ctx->createFreeVariable("{$frame->getType()}_val");
        $keyVar = $metadata->keysGeneratedBy() === TypedCollectionMetadata::KEY_PRESERVE ?
            $ctx->createFreeVariable("{$frame->getType()}_key") : null;

        $foreach = $ctx->foreachStmt($frame->getInputData(),
            new FunctionArgument($valVar->getName()), $keyVar ? new FunctionArgument($keyVar->getName()) : null);

        if ($metadata->getChildrenType()) {
            $ctx->pushFrame(new CompilationFrame($valVar, $metadata->getChildrenType()));
            $compiler->_compileType($ctx, $metadata->getChildrenType());
            $childFrame = $ctx->popFrame();
            $resultValVar = $childFrame->getResult();

            foreach ($childFrame->getStatements() as $stmt) {
                $foreach->addStatement($stmt);
            }
        }

        if ($metadata->keysGeneratedBy() !== TypedCollectionMetadata::KEY_INCREMENTING) {
            if ($metadata->keysGeneratedBy() === TypedCollectionMetadata::KEY_FROM_VALUE) {
                $keyVar = $valVar;
            }

            if ($metadata->getKeyType()) {
                $ctx->pushFrame(new CompilationFrame($keyVar, $metadata->getKeyType()));
                $compiler->_compileType($ctx, $metadata->getKeyType());
                $keyFrame = $ctx->popFrame();
                $keyVar = $keyFrame->getResult();

                foreach ($keyFrame->getStatements() as $stmt) {
                    $foreach->addStatement($stmt);
                }
            }
        }

        $foreach->addStatement($ctx->assignVariableStmt($ctx->fetchDim($colVar, $keyVar), $resultValVar));

        $frame->addStatement($foreach);
        $frame->setResult($colVar);
    }
}

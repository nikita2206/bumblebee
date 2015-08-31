<?php

namespace Bumblebee\TypeTransformer;

use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Compilation\CompilationFrame;
use Bumblebee\Compilation\ExpressionMethodCallable;
use Bumblebee\Compiler;
use Bumblebee\Metadata\ObjectArray\ObjectArrayElementMetadata;
use Bumblebee\Metadata\ObjectArray\ObjectArrayMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;
use Bumblebee\Transformer;

class ObjectArrayTransformer implements CompilableTypeTransformer
{

    /**
     * @param mixed $data
     * @param TypeMetadata $metadata
     * @param Transformer $transformer
     * @return mixed
     */
    public function transform($data, TypeMetadata $metadata, Transformer $transformer)
    {
        if ( ! $metadata instanceof ObjectArrayMetadata) {
            throw new \InvalidArgumentException();
        }

        $output = [];
        foreach ($metadata->getFields() as $field) {
            $fieldValue = $data;
            foreach ($field->getAccessorChain() as $accessor) {
                $fieldValue = $accessor->isMethod() ? $data->{$accessor->getName()}() : $data->{$accessor->getName()};
            }

            if ($field->getType()) {
                $fieldValue = $transformer->transform($fieldValue, $field->getType());
            }

            $output[$field->getName()] = $fieldValue;
        }

        return $output;
    }

    /**
     * @param ValidationContext $context
     * @param TypeMetadata $metadata
     * @return ValidationError[]
     */
    public function validateMetadata(ValidationContext $context, TypeMetadata $metadata)
    {
        $errors = [];

        if ( ! $metadata instanceof ObjectArrayMetadata) {
            $errors[] = new ValidationError(sprintf("%s expects instance of ObjectArrayMetadata, %s given", __CLASS__, get_class($metadata)));
        } else {
            $occuredNames = [];

            foreach ($metadata->getFields() as $idx => $field) {
                if ( ! $field instanceof ObjectArrayElementMetadata) {
                    $errors[] = new ValidationError(sprintf("Field#%s is of type %s, instance of %s expected",
                        $idx, is_object($field) ? get_class($field) : gettype($field), 'Bumblebee\Metadata\ObjectArray\ObjectArrayFieldMetadata'));
                } else {
                    if (isset($occuredNames[$field->getName()])) {
                        $errors[] = new ValidationError(sprintf("Field#%s '%s' has a duplicated name", $idx, $field->getName()));
                    }

                    if ($field->getType()) {
                        $context->validateLater($field->getType(), "{$context->getCurrentlyValidatingType()} > '{$field->getName()}'#{$idx}");
                    }

                    $occuredNames[$field->getName()] = true;
                }
            }
        }

        return $errors;
    }

    /**
     * @param CompilationContext $ctx
     * @param TypeMetadata $metadata
     * @param Compiler $compiler
     * @return void
     */
    public function compile(CompilationContext $ctx, TypeMetadata $metadata, Compiler $compiler)
    {
        if ( ! $metadata instanceof ObjectArrayMetadata) {
            throw new \InvalidArgumentException();
        }

        $frame = $ctx->getCurrentFrame();
        $input = $frame->getInputData();

        if ( ! $input instanceof ExpressionMethodCallable || ($input->evaluationComplexity() > 2 && count($metadata->getFields()) > 1)) {
            $inputVar = $ctx->createFreeVariable();
            $frame->addStatement($ctx->assignVariable($inputVar, $input));
            $input = $inputVar;
        }

        $outputArray = $ctx->arrayConstructor();
        foreach ($metadata->getFields() as $field) {
            $fieldValue = $input;
            foreach ($field->getAccessorChain() as $accessor) {
                $fieldValue = $accessor->isMethod() ? $ctx->callMethod($fieldValue, $accessor->getName()) : $ctx->fetchProperty($fieldValue, $accessor->getName());
            }

            if ($field->getType()) {
                $ctx->pushFrame(new CompilationFrame($fieldValue, $field->getType()));
                $compiler->_compileType($ctx, $field->getType());
                $fieldFrame = $ctx->popFrame();

                foreach ($fieldFrame->getStatements() as $stmt) {
                    $frame->addStatement($stmt);
                }
                $fieldValue = $fieldFrame->getResult();
            }

            $outputArray->add($field->getName(), $fieldValue);
        }

        $frame->setResult($outputArray);
    }

}

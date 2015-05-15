<?php

namespace Bumblebee\TypeTransformer;

use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Metadata\ObjectArrayFieldMetadata;
use Bumblebee\Metadata\ObjectArrayMetadata;
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
            $fieldValue = $field->isMethod() ? $data->{$field->getInputName()}() : $data->{$field->getInputName()};

            if ($field->getType()) {
                $fieldValue = $transformer->transform($fieldValue, $field->getType(), $transformer);
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
                if ( ! $field instanceof ObjectArrayFieldMetadata) {
                    $errors[] = new ValidationError(sprintf("Field#%s is of type %s, instance of %s expected",
                        $idx, is_object($field) ? get_class($field) : gettype($field), 'Bumblebee\Metadata\ObjectArrayFieldMetadata'));
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
     * @return void
     */
    public function compile(CompilationContext $ctx, TypeMetadata $metadata)
    {
        if ( ! $metadata instanceof ObjectArrayMetadata) {
            throw new \InvalidArgumentException();
        }

        foreach ($metadata->getFields() as $field) {
            $field->
        }
    }

}

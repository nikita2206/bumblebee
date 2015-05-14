<?php

namespace Bumblebee\TypeTransformer;

use Bumblebee\Metadata\DateTimeMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;
use Bumblebee\Transformer;

class DateTimeTextTransformer implements TypeTransformer
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
}
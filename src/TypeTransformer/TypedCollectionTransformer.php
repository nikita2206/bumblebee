<?php

namespace Bumblebee\TypeTransformer;


use Bumblebee\Metadata\TypedCollectionMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;
use Bumblebee\Transformer;

class TypedCollectionTransformer implements TypeTransformer
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
}

<?php

namespace Bumblebee\TypeTransformer;

use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;
use Bumblebee\TransformerInterface;

interface TypeTransformer
{

    /**
     * @param mixed $data
     * @param TypeMetadata $metadata
     * @param TransformerInterface|Transformer $transformer
     * @return mixed
     */
    public function transform($data, TypeMetadata $metadata, TransformerInterface $transformer);

    /**
     * @param ValidationContext $context
     * @param TypeMetadata $metadata
     * @return ValidationError[]
     */
    public function validateMetadata(ValidationContext $context, TypeMetadata $metadata);

}

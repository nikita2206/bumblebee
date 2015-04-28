<?php

namespace Bumblebee\TypeTransformer;
use Bumblebee\Exception\InvalidDataException;
use Bumblebee\Metadata\TypeMetadata;

/**
 * TODO: Next step is CompilableTypeTransformer
 */
interface TypeTransformer
{

    /**
     * @param mixed $data
     * @param TypeMetadata $metadata
     * @return mixed
     * @throws InvalidDataException
     */
    public function transform($data, TypeMetadata $metadata);

    /**
     * @param TypeMetadata $metadata
     * @return ValidationError[]
     */
    public function validateMetadata(TypeMetadata $metadata);

}

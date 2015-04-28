<?php

namespace Bumblebee\TypeTransformer;


use Bumblebee\Exception\InvalidDataException;
use Bumblebee\Metadata\TypeMetadata;

class ObjectArrayTransformer implements TypeTransformer
{

    /**
     * @param mixed $data
     * @param TypeMetadata $metadata
     * @return mixed
     * @throws InvalidDataException
     */
    public function transform($data, TypeMetadata $metadata)
    {

    }

}

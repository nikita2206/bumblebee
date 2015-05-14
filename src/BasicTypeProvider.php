<?php

namespace Bumblebee;

use Bumblebee\Exception\InvalidTypeException;
use Bumblebee\Metadata\TypeMetadata;

class BasicTypeProvider implements TypeProvider
{

    protected $types;

    /**
     * @param TypeMetadata[] $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * @param string $type
     * @return TypeMetadata
     * @throws InvalidTypeException
     */
    public function get($type)
    {
        if ( ! isset($this->types[$type])) {
            throw new InvalidTypeException($type);
        }

        return $this->types[$type];
    }

}

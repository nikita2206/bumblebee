<?php

namespace Bumblebee;

use Bumblebee\Exception\InvalidTypeException;
use Bumblebee\Metadata\TypeMetadata;

interface TypeProvider
{

    /**
     * @param string $type
     * @return TypeMetadata
     * @throws InvalidTypeException
     */
    public function get($type);

    /**
     * @return TypeMetadata[]|\Traversable
     */
    public function all();

}

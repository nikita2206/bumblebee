<?php

namespace Bumblebee;

use Bumblebee\Metadata\TypeMetadata;

interface TypeProvider
{

    /**
     * @param string $type
     * @return TypeMetadata
     * @throws InvalidTypeException
     */
    public function get($type);

}

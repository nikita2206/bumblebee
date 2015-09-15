<?php

namespace Bumblebee;

use Bumblebee\Exception\InvalidTypeException;

interface TransformerInterface
{
    /**
     * @param mixed $input
     * @param string $type
     * @return mixed
     * @throws InvalidTypeException
     */
    public function transform($input, $type);
}

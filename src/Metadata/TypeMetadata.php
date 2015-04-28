<?php

namespace Bumblebee\Metadata;

class TypeMetadata
{

    /**
     * @var string
     */
    public $transformer;

    public function __construct($transformer)
    {
        $this->transformer = $transformer;
    }

}

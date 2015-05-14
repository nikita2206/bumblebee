<?php

namespace Bumblebee\Metadata;

class TypeMetadata
{

    /**
     * @var string
     */
    protected $transformer;

    public function __construct($transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @return string
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

}

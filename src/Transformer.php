<?php

namespace Bumblebee;

use Bumblebee\Exception\InvalidTypeException;

class Transformer
{

    /**
     * @var TypeProvider
     */
    protected $types;

    /**
     * @var TransformerProvider
     */
    protected $transformers;

    public function __construct(TypeProvider $types, TransformerProvider $transformers)
    {
        $this->types = $types;
        $this->transformers = $transformers;
    }

    /**
     * @param mixed $input
     * @param string $type
     * @return mixed
     * @throws InvalidTypeException
     */
    public function transform($input, $type)
    {
        $typeMetadata = $this->types->get($type);
        $transformer  = $this->transformers->get($typeMetadata->getTransformer());

        return $transformer->transform($input, $typeMetadata, $this);
    }

}

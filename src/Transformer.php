<?php

namespace Bumblebee;

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
     * @throws InvalidDataException
     * @throws InvalidTypeException
     */
    public function transform($input, $type)
    {
        $typeMetadata = $this->types->get($type);
        $transformer  = $this->transformers->get($typeMetadata->transformer);

        return $transformer->transform($input, $typeMetadata->options);
    }

}

<?php

namespace Bumblebee;

use Bumblebee\Exception\InvalidTypeException;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;

class Transformer implements TransformerInterface
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

    /**
     * @return ValidationError[]
     */
    public function validateTypes()
    {
        $validationContext = new ValidationContext();
        $errors = [];

        foreach ($this->types->all() as $type => $metadata) {
            $validationContext->setCurrentlyValidatingType($type);
            $curErrors = $this->transformers->get($metadata->getTransformer())->validateMetadata($validationContext, $metadata);
            $validationContext->markValidated($type);

            if ($curErrors) {
                $errors[$type] = $curErrors;
            }
        }

        while ($queue = $validationContext->getDeferredQueueAndClean()) {
            foreach ($queue as $type => $askedFromTypes) {
                if ($validationContext->hasBeenValidated($type)) {
                    continue;
                }

                try {
                    $metadata = $this->types->get($type);

                    $validationContext->setCurrentlyValidatingType($type);
                    $curErrors = $this->transformers->get($metadata->getTransformer())->validateMetadata($validationContext, $metadata);
                    $validationContext->markValidated($type);

                    if ($curErrors) {
                        $errors[$type] = $curErrors;
                    }
                } catch (InvalidTypeException $e) {
                    $errors[$type][] = new ValidationError(sprintf("Type wasn't found, was referenced from: %s", implode(", ", $askedFromTypes)));
                }
            }
        }

        return $errors;
    }

}

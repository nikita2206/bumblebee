<?php

namespace Bumblebee\Metadata;

class ValidationContext
{

    protected $deferredValidationQueue;

    protected $typesValidated;

    protected $currentType;

    public function __construct()
    {
        $this->deferredValidationQueue = [];
        $this->typesValidated = [];
        $this->currentType = null;
    }

    public function validateLater($type, $askedFrom)
    {
        $this->deferredValidationQueue[$type][] = $askedFrom;
    }

    public function markValidated($type)
    {
        $this->typesValidated[$type] = true;
    }

    public function getCurrentlyValidatingType()
    {
        return $this->currentType;
    }

    public function setCurrentlyValidatingType($type)
    {
        $this->currentType = $type;
    }

}

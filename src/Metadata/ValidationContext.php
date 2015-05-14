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
        if ( ! $this->hasBeenValidated($type)) {
            $this->deferredValidationQueue[$type][] = $askedFrom;
        }
    }

    public function getDeferredQueueAndClean()
    {
        $queue = $this->deferredValidationQueue;
        $this->deferredValidationQueue = [];
        return $queue;
    }

    public function markValidated($type)
    {
        $this->typesValidated[$type] = true;
    }

    public function hasBeenValidated($type)
    {
        return isset($this->typesValidated[$type]);
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

<?php

namespace Bumblebee\Metadata;

class DateTimeMetadata extends TypeMetadata
{

    protected $format;

    public function __construct($format)
    {
        parent::__construct("datetime_text");

        $this->format = $format;
    }

    public function getFormat()
    {
        return $this->format;
    }

}

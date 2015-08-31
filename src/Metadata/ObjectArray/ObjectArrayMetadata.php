<?php

namespace Bumblebee\Metadata\ObjectArray;

use Bumblebee\Metadata\TypeMetadata;

class ObjectArrayMetadata extends TypeMetadata
{

    protected $fields;

    /**
     * @param ObjectArrayElementMetadata[] $fields
     */
    public function __construct(array $fields)
    {
        parent::__construct("object_array");

        $this->fields = $fields;
    }

    public function addField(ObjectArrayElementMetadata $field)
    {
        $this->fields[] = $field;
    }

    /**
     * @return ObjectArrayElementMetadata[]
     */
    public function getFields()
    {
        return $this->fields;
    }

}

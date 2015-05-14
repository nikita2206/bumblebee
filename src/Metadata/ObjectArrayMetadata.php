<?php

namespace Bumblebee\Metadata;


class ObjectArrayMetadata extends TypeMetadata
{

    protected $fields;

    /**
     * @param ObjectArrayFieldMetadata[] $fields
     */
    public function __construct(array $fields)
    {
        parent::__construct("object_array");

        $this->fields = $fields;
    }

    public function addField(ObjectArrayFieldMetadata $field)
    {
        $this->fields[] = $field;
    }

    /**
     * @return ObjectArrayFieldMetadata[]
     */
    public function getFields()
    {
        return $this->fields;
    }

}

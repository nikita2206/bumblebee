<?php

namespace Bumblebee\Metadata\ObjectArray;

class ObjectArrayElementMetadata
{

    protected $type;

    protected $name;

    protected $accessorChain;

    /**
     * @param string|null $type
     * @param string $name
     * @param ObjectArrayAccessorMetadata[] $accessorChain
     */
    public function __construct($type, $name, array $accessorChain)
    {
        $this->type = $type;
        $this->name = $name;
        $this->accessorChain = $accessorChain;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ObjectArrayAccessorMetadata[]
     */
    public function getAccessorChain()
    {
        return $this->accessorChain;
    }

}

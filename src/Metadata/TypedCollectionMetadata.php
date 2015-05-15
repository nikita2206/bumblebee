<?php

namespace Bumblebee\Metadata;

class TypedCollectionMetadata extends TypeMetadata
{

    protected $childrenType;

    protected $transformIntoArray;

    protected $collectionClassName;

    protected $preserveKeys;

    /**
     * @param string|null $childrenType
     * @param bool $transformIntoArray
     * @param string|null $collectionClassName
     * @param bool $preserveKeys
     */
    public function __construct($childrenType, $transformIntoArray = true, $collectionClassName = null, $preserveKeys = false)
    {
        parent::__construct("typed_collection");

        $this->childrenType = $childrenType;
        $this->transformIntoArray = $transformIntoArray;
        $this->collectionClassName = $collectionClassName;
        $this->preserveKeys = $preserveKeys;
    }

    /**
     * @return null|string
     */
    public function getChildrenType()
    {
        return $this->childrenType;
    }

    /**
     * @return bool
     */
    public function shouldTransformIntoArray()
    {
        return $this->transformIntoArray;
    }

    /**
     * @return null|string
     */
    public function getCollectionClassName()
    {
        return $this->collectionClassName;
    }

    /**
     * @return bool
     */
    public function shouldPreserveKeys()
    {
        return $this->preserveKeys;
    }

}

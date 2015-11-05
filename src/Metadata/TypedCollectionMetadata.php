<?php

namespace Bumblebee\Metadata;

class TypedCollectionMetadata extends TypeMetadata
{
    const KEY_INCREMENTING = 0,
          KEY_PRESERVE     = 1,
          KEY_FROM_VALUE   = 2;

    protected $childrenType;

    protected $transformIntoArray;

    protected $collectionClassName;

    protected $keysGeneratedBy;

    protected $keyType;

    /**
     * @param string|null $childrenType
     * @param bool $transformIntoArray
     * @param string|null $collectionClassName
     * @param int $keysGeneratedBy
     * @param string $keyType
     */
    public function __construct($childrenType, $transformIntoArray = true, $collectionClassName = null, $keysGeneratedBy = self::KEY_INCREMENTING, $keyType = null)
    {
        parent::__construct("typed_collection");

        $this->childrenType = $childrenType;
        $this->transformIntoArray = $transformIntoArray;
        $this->collectionClassName = $collectionClassName;
        $this->keysGeneratedBy = $keysGeneratedBy;
        $this->keyType = $keyType;
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
     * @return int
     */
    public function keysGeneratedBy()
    {
        return $this->keysGeneratedBy;
    }

    /**
     * @return null|string
     */
    public function getKeyType()
    {
        return $this->keyType;
    }
}

<?php

namespace Bumblebee\Metadata\ArrayToObject;

class ArrayToObjectArgumentMetadata
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $arrayKey;

    /**
     * @var bool
     */
    protected $assumeAlwaysSet;

    /**
     * @var array|float|int|null|string
     */
    protected $fallback;

    /**
     * @param string $type
     * @param array $arrayKey A sequence of array keys
     * @param bool $assumeAlwaysSet
     * @param null|string|float|int|array $fallback
     */
    public function __construct($type, array $arrayKey, $assumeAlwaysSet = true, $fallback = null)
    {
        $this->type = $type;
        $this->arrayKey = $arrayKey;
        $this->assumeAlwaysSet = $assumeAlwaysSet;
        $this->fallback = $fallback;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getArrayKey()
    {
        return $this->arrayKey;
    }

    /**
     * @return bool
     */
    public function isKeyAlwaysSet()
    {
        return $this->assumeAlwaysSet;
    }

    /**
     * @return null|string|float|int|array
     */
    public function getFallbackData()
    {
        return $this->fallback;
    }

}

<?php

namespace Bumblebee\Metadata;

class ArrayToObjectArgumentMetadata
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
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
     * @param string $arrayKey
     * @param bool $assumeAlwaysSet
     * @param null|string|float|int|array $fallback
     */
    public function __construct($type, $arrayKey, $assumeAlwaysSet = true, $fallback = null)
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
     * @return string
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

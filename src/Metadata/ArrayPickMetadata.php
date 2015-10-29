<?php

namespace Bumblebee\Metadata;

class ArrayPickMetadata extends TypeMetadata
{
    /**
     * @var array
     */
    protected $path;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @param array $path
     * @param mixed $default
     */
    public function __construct(array $path, $default = null)
    {
        parent::__construct("array_pick");

        $this->path = $path;
        $this->default = $default;
    }

    /**
     * @return array
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }
}

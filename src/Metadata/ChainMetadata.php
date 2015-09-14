<?php

namespace Bumblebee\Metadata;

class ChainMetadata extends TypeMetadata
{

    /**
     * @var string[] Transformation type names
     */
    protected $chain;

    public function __construct(array $chain)
    {
        parent::__construct("chain");

        $this->chain = $chain;
    }

    public function getChain()
    {
        return $this->chain;
    }
}

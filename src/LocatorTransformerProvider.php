<?php

namespace Bumblebee;

use Bumblebee\TypeTransformer\TypeTransformer;

class LocatorTransformerProvider implements TransformerProvider
{

    protected $transformerClassMap;

    /**
     * @var TypeTransformer[]
     */
    protected $instantiated;

    /**
     * @param string[] $transformerClassMap Map<TransformerName, ClassName>
     */
    public function __construct($transformerClassMap)
    {
        $this->transformerClassMap = $transformerClassMap;
    }

    /**
     * @param string $transformer
     * @return TypeTransformer
     */
    public function get($transformer)
    {
        if ( ! isset($this->transformerClassMap[$transformer])) {
            throw new \RuntimeException("There's no transformer '{$transformer}'");
        }

        return isset($this->instantiated[$transformer]) ? $this->instantiated[$transformer]
            : $this->instantiated[$transformer] = new $this->transformerClassMap[$transformer]();
    }

}

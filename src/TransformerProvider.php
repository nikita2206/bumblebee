<?php

namespace Bumblebee;

use Bumblebee\TypeTransformer\TypeTransformer;

interface TransformerProvider
{

    /**
     * @param string $transformer
     * @return TypeTransformer
     */
    public function get($transformer);

}

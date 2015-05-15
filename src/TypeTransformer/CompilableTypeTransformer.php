<?php

namespace Bumblebee\TypeTransformer;

use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Metadata\TypeMetadata;

interface CompilableTypeTransformer extends TypeTransformer
{

    /**
     * Compiles transformer for a given metadata
     *
     * @param CompilationContext $ctx
     * @param TypeMetadata $metadata
     * @return void
     */
    public function compile(CompilationContext $ctx, TypeMetadata $metadata);

}

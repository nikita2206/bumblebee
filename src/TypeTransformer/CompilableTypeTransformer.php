<?php

namespace Bumblebee\TypeTransformer;

use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Compiler;
use Bumblebee\Metadata\TypeMetadata;

interface CompilableTypeTransformer extends TypeTransformer
{
    /**
     * Compiles transformer for a given metadata
     *
     * @param CompilationContext $ctx
     * @param TypeMetadata $metadata
     * @param Compiler $compiler
     * @return void
     */
    public function compile(CompilationContext $ctx, TypeMetadata $metadata, Compiler $compiler);
}

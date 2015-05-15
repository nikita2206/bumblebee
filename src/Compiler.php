<?php

namespace Bumblebee;

use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Compilation\CompilationFrame;
use Bumblebee\Compilation\Variable;
use Bumblebee\TypeTransformer\CompilableTypeTransformer;

class Compiler
{

    /**
     * @var TypeProvider
     */
    protected $types;

    /**
     * @var TransformerProvider
     */
    protected $transformers;

    public function __construct(TypeProvider $types, TransformerProvider $transformers)
    {
        $this->types = $types;
        $this->transformers = $transformers;
    }

    /**
     * @param string $type
     * @return string
     */
    public function compile($type)
    {
        $ctx = new CompilationContext(new Variable("input"), new Variable("transformer"));

        $metadata = $this->types->get($type);
        $transformer = $this->transformers->get($metadata->getTransformer());

        if ( ! $transformer instanceof CompilableTypeTransformer) {
            // fallback to just call a Transformer#transform() method is the current transformer is not compilable
            $methodCall = $ctx->callMethod($ctx->getTransformerVariable(), "transform", [$ctx->getInputVariable(), $ctx->constValue($type)]);
            return $ctx->generateCallback([$ctx->returnStatement($methodCall)]);
        }

        $rootFrame = new CompilationFrame($ctx->getInputVariable());
        $ctx->pushFrame($rootFrame);

        $transformer->compile($ctx, $metadata);

        $stmts = $rootFrame->getStatements();
        $stmts[] = $ctx->returnStatement($rootFrame->getResult());

        return $ctx->generateCallback($stmts);
    }

}

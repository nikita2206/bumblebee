<?php

namespace Bumblebee;

use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Compilation\CompilationFrame;
use Bumblebee\Compilation\FunctionArgument;
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
        $ctx->pushFrame($frame = new CompilationFrame($ctx->getInputVariable(), $type));

        $this->_compileType($ctx, $type);

        $stmts = $frame->getStatements();
        $stmts[] = $ctx->returnStatement($frame->getResult());

        return $ctx->generateCallback($stmts);
    }

    /**
     * Acts on a $ctx->currentFrame, doesn't return anything
     *
     * @param CompilationContext $ctx
     * @param string $type
     */
    public function _compileType(CompilationContext $ctx, $type)
    {
        $frame = $ctx->getCurrentFrame();
        $metadata = $this->types->get($type);
        $transformer = $this->transformers->get($metadata->getTransformer());

        if ( ! $transformer instanceof CompilableTypeTransformer) {
            $frame->setResult($ctx->callMethod($ctx->getTransformerVariable(), "transform", [$frame->getInputData(), $ctx->compileTimeValue($type)]));
            return;
        }

        if ($ctx->isCurrentFrameInRecursion()) {
            if ( ! $recursiveTransformer = $ctx->getRecursiveTransformer($type)) {
                $recursiveTransformer = $ctx->createFreeVariable("{$type}_trans");
                $innerCtx = new CompilationContext(new Variable("input"), new Variable("transformer"));
                $ctx->addRecursiveTransformer($type, $recursiveTransformer);
                $innerCtx->setRecursiveTransformers($ctx->getRecursiveTransformers());
                $innerCtx->pushFrame($innerFrame = new CompilationFrame($innerCtx->getInputVariable(), $type));

                $transformer->compile($innerCtx, $metadata, $this);
                $stmts = $innerFrame->getStatements();
                $stmts[] = $innerCtx->returnStatement($innerFrame->getResult());

                $functionUses = [new FunctionArgument("transformer"), new FunctionArgument($recursiveTransformer->getName(), true)];
                foreach ($ctx->getRecursiveTransformers() as $trans) {
                    if ($trans->getName() !== $recursiveTransformer->getName()) {
                        $functionUses[] = new FunctionArgument($trans->getName());
                    }
                }
                $function = $innerCtx->anonymousFunction([new FunctionArgument("input")], $stmts, $functionUses);

                $frame->addStatement($ctx->assignVariableStmt($recursiveTransformer, $function));
            }

            $frame->setResult($ctx->callFunction($recursiveTransformer, [$frame->getInputData()]));
        } else {
            $transformer->compile($ctx, $metadata, $this);
        }
    }

}

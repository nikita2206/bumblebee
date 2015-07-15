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
     * Until which level should compiler inline transformation of recursive types.
     * When type recursion hits this level, compiler will create a recursive closure
     * for this type and use it from that point.
     * Sometimes you have recursive types but you know that they will always be
     * of constant depth then you can set this option to the depth you are expecting
     * in order to save on some call stack frames when transforming.
     *
     * @var int
     */
    public $inlineRecursiveTypesUntilLevel = 0;

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
     * Returns generated code of an anonymous function for transforming given type
     *
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
     * Acts on a $ctx->currentFrame
     *
     * @param CompilationContext $ctx
     * @param string $type
     * @return void
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

        $lvl = $ctx->getCurrentFrameRecursionLevel();
        $recursiveTransformerVar = $ctx->getRecursiveTransformer($type);
        if ($recursiveTransformerVar || $lvl > $this->inlineRecursiveTypesUntilLevel) {
            if ($recursiveTransformerVar === null) {
                $recursiveTransformerVar = $ctx->createFreeVariable("{$type}_trans");
                $innerCtx = new CompilationContext(new Variable("input"), new Variable("transformer"));
                $ctx->addRecursiveTransformer($type, $recursiveTransformerVar);
                $innerCtx->setRecursiveTransformers($ctx->getRecursiveTransformers());
                $innerCtx->pushFrame($innerFrame = new CompilationFrame($innerCtx->getInputVariable(), $type));

                $transformer->compile($innerCtx, $metadata, $this);
                $stmts = $innerFrame->getStatements();
                $stmts[] = $innerCtx->returnStatement($innerFrame->getResult());

                $functionUses = [new FunctionArgument("transformer"), new FunctionArgument($recursiveTransformerVar->getName(), true)];
                foreach ($ctx->getRecursiveTransformers() as $trans) {
                    if ($trans !== $recursiveTransformerVar) {
                        $functionUses[] = new FunctionArgument($trans->getName());
                    }
                }
                $function = $innerCtx->anonymousFunction([new FunctionArgument("input")], $stmts, $functionUses);
                $ctx->declareRecursiveTransformer($recursiveTransformerVar, $function);
            }

            $frame->setResult($ctx->callFunction($recursiveTransformerVar, [$frame->getInputData()]));
        } else {
            $transformer->compile($ctx, $metadata, $this);
        }
    }

}

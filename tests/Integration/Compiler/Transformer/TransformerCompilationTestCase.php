<?php

namespace Bumblebee\Tests\Integration\Compiler\Transformer;

use Bumblebee\BasicTypeProvider;
use Bumblebee\Compiler;
use Bumblebee\LocatorTransformerProvider;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Transformer;

class TransformerCompilationTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @param string $type
     * @param TypeMetadata[] $typeDefinitions
     * @return \Closure
     */
    public function generateTransformer($type, array $typeDefinitions)
    {
        $transformers = new LocatorTransformerProvider([
            "object_array" => 'Bumblebee\TypeTransformer\ObjectArrayTransformer',
            "datetime_text" => 'Bumblebee\TypeTransformer\DateTimeTextTransformer',
            "typed_collection" => 'Bumblebee\TypeTransformer\TypedCollectionTransformer',
            "array_to_object" => 'Bumblebee\TypeTransformer\ArrayToObjectTransformer'
        ]);

        $metadatas = new BasicTypeProvider($typeDefinitions);

        $compiler = new Compiler($metadatas, $transformers);

        return eval("return {$compiler->compile($type)};");
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|Transformer
     */
    protected function getFakeTransformer($methods = [])
    {
        return $this->getMock('Bumblebee\Transformer', $methods, [], '', false);
    }

}

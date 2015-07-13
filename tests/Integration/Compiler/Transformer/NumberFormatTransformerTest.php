<?php

namespace Bumblebee\Tests\Integration\Compiler\Transformer;

use Bumblebee\Metadata\NumberFormatMetadata;

class NumberFormatTransformerTest extends TransformerCompilationTestCase
{

    public function testBasicCase()
    {
        $t = $this->generateTransformer("foo", [
            "foo" => new NumberFormatMetadata(3)
        ]);
        $transformer = $this->getFakeTransformer();

        $result = $t(4678987.68789, $transformer);
        $this->assertSame("4,678,987.688", $result);
    }

}

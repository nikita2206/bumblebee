<?php

namespace Bumblebee\Tests\Integration\Compiler\Transformer;

use Bumblebee\Metadata\NumberFormatMetadata;
use Bumblebee\Metadata\TypedCollectionMetadata;

class TypedCollectionTransformerTest extends TransformerCompilationTestCase
{
    public function testBasicCase()
    {
        $t = $this->generateTransformer("foo", [
            "foo" => new TypedCollectionMetadata(null)
        ]);
        $transformer = $this->getFakeTransformer();

        $result = $t([
            0, 1, 2, "Qwe" => "asd", "zxc" => 3
        ], $transformer);

        $this->assertSame([0, 1, 2, "asd", 3], $result);
    }

    public function testTypedCase()
    {
        $t = $this->generateTransformer("foo", [
            "foo" => new TypedCollectionMetadata("bar"),
            "bar" => new NumberFormatMetadata(2)
        ]);
        $transformer = $this->getFakeTransformer();

        $input = [1, 2.123, 12312423, .234, 4564562443.123];

        $expected = ["1.00", "2.12", "12,312,423.00", "0.23", "4,564,562,443.12"];

        $resultFromArray = $t($input, $transformer);
        $this->assertSame($expected, $resultFromArray);

        $resultFromIterator = $t(new \ArrayObject($input), $transformer);
        $this->assertSame($expected, $resultFromIterator);
    }

    public function testPreserveKeys()
    {
        $t = $this->generateTransformer("foo", [
            "foo" => new TypedCollectionMetadata(null, true, null, true)
        ]);
        $transformer = $this->getFakeTransformer();

        $input = [
            "q" => "q", "a" => "a", "z" => "z", 4 => "f", 1 => "d"
        ];
        $result = $t($input, $transformer);

        $this->assertSame($input, $result);
    }

    public function testTransformIntoArrayObject()
    {
        $t = $this->generateTransformer("foo", [
            "foo" => new TypedCollectionMetadata(null, false, "ArrayObject")
        ]);
        $transformer = $this->getFakeTransformer();

        $input = [1, 2, 3, 4];
        $result = $t($input, $transformer);

        $this->assertInstanceOf("ArrayObject", $result);
        $this->assertSame($input, $result->getArrayCopy());
    }
}

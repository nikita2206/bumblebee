<?php

namespace Bumblebee\Tests\Integration\Compiler\Transformer;

use Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata;
use Bumblebee\Metadata\ObjectArray\ObjectArrayElementMetadata;
use Bumblebee\Metadata\ObjectArray\ObjectArrayMetadata;

class ObjectArrayTransformerTest extends TransformerCompilationTestCase
{

    public function testBasicCase()
    {
        $t = $this->generateTransformer("foo", [
            "foo" => new ObjectArrayMetadata([
                new ObjectArrayElementMetadata("bar", 0, [new ObjectArrayAccessorMetadata("getFoo")]),
                new ObjectArrayElementMetadata(null, 1, [new ObjectArrayAccessorMetadata("getBar")]),
                new ObjectArrayElementMetadata(null, "apple", [new ObjectArrayAccessorMetadata("apple", false)])
            ]),
            "bar" => new ObjectArrayMetadata([
                new ObjectArrayElementMetadata(null, 1, [new ObjectArrayAccessorMetadata("getBar")]),
                new ObjectArrayElementMetadata(null, "apple", [new ObjectArrayAccessorMetadata("apple", false)])
            ])
        ]);
        $transformer = $this->getFakeTransformer();

        $data = new ObjectArrayTransformerTestSubject(new ObjectArrayTransformerTestSubject(null, "qwe"), "bar");
        $data->apple = "fruit";

        $result = $t($data, $transformer);

        $this->assertSame([
            0 => [1 => "qwe", "apple" => null],
            1 => "bar",
            "apple" => "fruit"
        ], $result);
    }

}

class ObjectArrayTransformerTestSubject
{

    protected $foo, $bar;

    public $apple;

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function getBar()
    {
        return $this->bar;
    }
}

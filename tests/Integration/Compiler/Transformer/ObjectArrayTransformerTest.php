<?php

namespace Bumblebee\Tests\Integration\Compiler\Transformer;

use Bumblebee\Metadata\ObjectArrayFieldMetadata;
use Bumblebee\Metadata\ObjectArrayMetadata;

class ObjectArrayTransformerTest extends TransformerCompilationTestCase
{

    public function testBasicCase()
    {
        $t = $this->generateTransformer("foo", [
            "foo" => new ObjectArrayMetadata([
                new ObjectArrayFieldMetadata("bar", 0, "getFoo", true),
                new ObjectArrayFieldMetadata(null, 1, "getBar", true),
                new ObjectArrayFieldMetadata(null, "apple", "apple", false)
            ]),
            "bar" => new ObjectArrayMetadata([
                new ObjectArrayFieldMetadata(null, 1, "getBar", true),
                new ObjectArrayFieldMetadata(null, "apple", "apple", false)
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

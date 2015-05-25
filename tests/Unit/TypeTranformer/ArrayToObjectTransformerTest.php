<?php

namespace Bumblebee\Tests\Unit\TypeTransformer;

use Bumblebee\Compilation\ConstructObject;
use Bumblebee\Compilation\ConstValue;
use Bumblebee\Compilation\Variable;
use Bumblebee\Metadata\ArrayToObjectArgumentMetadata;
use Bumblebee\Metadata\ArrayToObjectMetadata;
use Bumblebee\Metadata\ArrayToObjectSettingMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Transformer;
use Bumblebee\TypeTransformer\ArrayToObjectTransformer;

class ArrayToObjectTransformerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|Transformer
     */
    protected function getFakeTransformer($methods = [])
    {
        return $this->getMock('Bumblebee\Transformer', $methods, [], '', false);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testTransformValidatesInput()
    {
        $t = new ArrayToObjectTransformer();

        $t->transform(null, new ArrayToObjectMetadata(""), $this->getFakeTransformer());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTransformValidatesMetadata()
    {
        $t = new ArrayToObjectTransformer();

        $t->transform([], new TypeMetadata(""), $this->getFakeTransformer());
    }

    public function testTransformBasicCase()
    {
        $dataToTransform = ["foo" => "bar", "foobar" => "barfoo", "fruit" => "orange", "veggie" => "tomato"];

        $t = new ArrayToObjectTransformer();

        $metadata = new ArrayToObjectMetadata('Bumblebee\Tests\Unit\TypeTransformer\TestDataClass', [
            new ArrayToObjectArgumentMetadata(null, "bar", false, "foo"),
            new ArrayToObjectArgumentMetadata(null, "foobar")
        ], [
            new ArrayToObjectSettingMetadata("foo", [new ArrayToObjectArgumentMetadata(null, "foo")], false),
            new ArrayToObjectSettingMetadata("setGroceries", [
                new ArrayToObjectArgumentMetadata(null, "fruit"),
                new ArrayToObjectArgumentMetadata(null, "veggie")
            ])
        ]);

        /** @var TestDataClass $transformed */
        $transformed = $t->transform($dataToTransform, $metadata, $this->getFakeTransformer());

        $this->assertSame("foo", $transformed->getBar());
        $this->assertSame($dataToTransform["foobar"], $transformed->getFoobar());
        $this->assertSame($dataToTransform["fruit"], $transformed->getFruit());
        $this->assertSame($dataToTransform["veggie"], $transformed->getVeggie());
        $this->assertSame($dataToTransform["foo"], $transformed->foo);
    }

    public function testTransformElementWithTypeDefined()
    {
        $dataToTransform = ["foo" => "bar"];

        $t = new ArrayToObjectTransformer();

        $metadata = new ArrayToObjectMetadata('Bumblebee\Tests\Unit\TypeTransformer\TestDataClass', [
            new ArrayToObjectArgumentMetadata("custom_type", "foo"),
            new ArrayToObjectArgumentMetadata(null, "bar", false)
        ]);

        $transformer = $this->getFakeTransformer(["transform"]);
        $transformer->expects($this->once())->method("transform")->with("bar", "custom_type")->will($this->returnValue("barbar"));

        /** @var TestDataClass $transformed */
        $transformed = $t->transform($dataToTransform, $metadata, $transformer);

        $this->assertSame("barbar", $transformed->getBar());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCompileThrowsInvalidArgumentException()
    {
        $t = new ArrayToObjectTransformer();

        $t->compile(
            $this->getMock('Bumblebee\Compilation\CompilationContext', [], [], '', false),
            new TypeMetadata(""),
            $this->getMock('Bumblebee\Compiler', [], [], '', false)
        );
    }

    public function testCompileAlwaysSetsResult()
    {
        $t = new ArrayToObjectTransformer();

        foreach ([new ArrayToObjectMetadata("stdClass"), new ArrayToObjectMetadata("stdClass", [], [
            new ArrayToObjectSettingMetadata("foo", [new ArrayToObjectArgumentMetadata(null, "foo")], false)
        ])] as $metadata) {
            $frame = $this->getMock('Bumblebee\Compilation\CompilationFrame', [], ["getInputData", "addStatement", "setResult"], '', false);
            $frame->expects($this->any())->method("getInputData")->will($this->returnValue(new Variable("input")));
            $frame->expects($this->any())->method("addStatement");
            $frame->expects($this->atLeastOnce())->method("setResult");

            $ctx = $this->getMock('Bumblebee\Compilation\CompilationContext', ["getCurrentFrame"], [new Variable("input"), new Variable("transformer")]);
            $ctx->expects($this->any())->method("getCurrentFrame")->will($this->returnValue($frame));

            $t->compile($ctx, $metadata, $this->getMock('Bumblebee\Compiler', [], [], "", false));
        }
    }

    public function testValidation()
    {
        $t = new ArrayToObjectTransformer();

        $errors = $t->validateMetadata(new ValidationContext(), $md = new TypeMetadata(""));
        $this->assertCount(1, $errors);
        $this->assertSame('Bumblebee\TypeTransformer\ArrayToObjectTransformer expects ' .
            'instance of ArrayToObjectMetadata, ' . get_class($md) . ' given', $errors[0]->getMessage());

        $ctx = $this->getMock('Bumblebee\Metadata\ValidationContext', ["getCurrentlyValidatingType", "validateLater"], [], "", false);
        $ctx->expects($this->any())->method("getCurrentlyValidatingType")->will($this->returnValue("root_type"));
        $ctx->expects($this->once())->method("validateLater")->with("deferred_type", "root_type -> __construct -> Arg#0");

        $errors = $t->validateMetadata($ctx, new ArrayToObjectMetadata('stdClass', [
            new ArrayToObjectArgumentMetadata("deferred_type", "ctor_arg0", false, new \stdClass())
        ], [
            new ArrayToObjectSettingMetadata("prop", [
                new ArrayToObjectArgumentMetadata(null, "propVal"),
                new ArrayToObjectArgumentMetadata(null, "extraVal")
            ], false)
        ]));

        $this->assertCount(2, $errors);
        $this->assertSame("__construct argument#0 (arrayKey=ctor_arg0) can't have fallback of type resource or object", $errors[0]->getMessage());
        $this->assertSame("Property assigning expects only one argument, 2 given for property prop", $errors[1]->getMessage());
    }

}

class TestDataClass
{

    public $foo;

    protected $bar;

    protected $foobar;

    protected $fruit;

    protected $vegetable;

    public function __construct($bar, $foobar)
    {
        $this->bar = $bar;
        $this->foobar = $foobar;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function getFoobar()
    {
        return $this->foobar;
    }

    public function getFruit()
    {
        return $this->fruit;
    }

    public function getVeggie()
    {
        return $this->vegetable;
    }

    public function setGroceries($fruit, $veggie)
    {
        $this->fruit = $fruit;
        $this->vegetable = $veggie;
    }

}

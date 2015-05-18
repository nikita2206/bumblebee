<?php

namespace Bumblebee\Tests\Unit\TypeTransformer;

use Bumblebee\Metadata\ArrayToObjectArgumentMetadata;
use Bumblebee\Metadata\ArrayToObjectMetadata;
use Bumblebee\Metadata\ArrayToObjectSettingMetadata;
use Bumblebee\Metadata\TypeMetadata;
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

    public function testTransformValidatesInput()
    {
        $t = new ArrayToObjectTransformer();

        $this->setExpectedException('RuntimeException');
        $t->transform(null, new ArrayToObjectMetadata(""), $this->getFakeTransformer());
    }

    public function testTransformValidatesMetadata()
    {
        $t = new ArrayToObjectTransformer();

        $this->setExpectedException('InvalidArgumentException');
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

    public function testElementWithTypeDefined()
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

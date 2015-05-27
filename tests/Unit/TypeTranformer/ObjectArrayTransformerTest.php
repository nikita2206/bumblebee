<?php

namespace Bumblebee\Tests\Unit\TypeTransformer;

use Bumblebee\Compilation\Variable;
use Bumblebee\Metadata\ObjectArrayFieldMetadata;
use Bumblebee\Metadata\ObjectArrayMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Transformer;
use Bumblebee\TypeTransformer\ObjectArrayTransformer;

class ObjectArrayTransformerTest extends \PHPUnit_Framework_TestCase
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
     * @expectedException \InvalidArgumentException
     */
    public function testTransformChecksMetadata()
    {
        $t = new ObjectArrayTransformer();

        $t->transform((object)[], new TypeMetadata(""), $this->getFakeTransformer());
    }

    public function testTransformBasicCase()
    {
        $subj = new TransformSubj("foo", "bar");
        $t = new ObjectArrayTransformer();
        $transformer = $this->getFakeTransformer();

        $result = $t->transform($subj, new ObjectArrayMetadata([
            new ObjectArrayFieldMetadata(null, "first", "getFoo", true),
            new ObjectArrayFieldMetadata(null, "second", "bar", false)
        ]), $transformer);
        $this->assertSame(["first" => "foo", "second" => "bar"], $result);


    }

    public function testTransformWithType()
    {
        $subj = new TransformSubj("foo", "bar");
        $t = new ObjectArrayTransformer();

        $transformer = $this->getFakeTransformer(["transform"]);
        $transformer->expects($this->once())->method("transform")->with("foo", "fooType")->will($this->returnValue("trans_foo"));

        $result = $t->transform($subj, new ObjectArrayMetadata([
            new ObjectArrayFieldMetadata("fooType", "first", "getFoo", true),
            new ObjectArrayFieldMetadata(null, "second", "bar", false)
        ]), $transformer);
        $this->assertSame(["first" => "trans_foo", "second" => "bar"], $result);
    }

    public function testCompileSetsResult()
    {
        $t = new ObjectArrayTransformer();

        $frame = $this->getMock('Bumblebee\Compilation\CompilationFrame', [], ["getInputData", "addStatement", "setResult"], '', false);
        $frame->expects($this->any())->method("getInputData")->will($this->returnValue(new Variable("input")));
        $frame->expects($this->any())->method("addStatement");
        $frame->expects($this->atLeastOnce())->method("setResult");

        $ctx = $this->getMock('Bumblebee\Compilation\CompilationContext', ["getCurrentFrame"], [new Variable("input"), new Variable("transformer")]);
        $ctx->expects($this->any())->method("getCurrentFrame")->will($this->returnValue($frame));

        $t->compile($ctx, new ObjectArrayMetadata([]), $this->getMock('Bumblebee\Compiler', [], [], "", false));
    }

    public function testValidation()
    {
        $t = new ObjectArrayTransformer();
        $ctx = $this->getMock('Bumblebee\Metadata\ValidationContext', ["validateLater"], [], "", false);
        $ctx->expects($this->once())->method("validateLater")->with("deferred_type");

        $errors = $t->validateMetadata($ctx, new ObjectArrayMetadata([
            new ObjectArrayFieldMetadata("deferred_type", "foo", "foo", false),
            new ObjectArrayFieldMetadata(null, "repeated", "bar", false),
            new ObjectArrayFieldMetadata(null, "repeated", "bar", false),
            new \stdClass()
        ]));

        $this->assertCount(2, $errors);
        $this->assertSame("Field#2 'repeated' has a duplicated name", $errors[0]->getMessage());
        $this->assertSame('Field#3 is of type stdClass, instance of Bumblebee\Metadata\ObjectArrayFieldMetadata expected', $errors[1]->getMessage());
    }

}

class TransformSubj
{

    protected $foo;

    public $bar;

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function getFoo()
    {
        return $this->foo;
    }

}

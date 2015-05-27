<?php

namespace Bumblebee\Tests\Unit\TypeTransformer;

use Bumblebee\Compilation\Variable;
use Bumblebee\Metadata\TypedCollectionMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Transformer;
use Bumblebee\TypeTransformer\TypedCollectionTransformer;

class TypedCollectionTransformerTest extends \PHPUnit_Framework_TestCase
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
        $t = new TypedCollectionTransformer();

        $t->transform([], new TypeMetadata(""), $this->getFakeTransformer());
    }

    public function testTransformBasicCase()
    {
        $subj = ["as", "qw"];
        $t = new TypedCollectionTransformer();

        $result = $t->transform($subj, new TypedCollectionMetadata(null), $this->getFakeTransformer());
        $this->assertSame($subj, $result);
    }

    public function testTransformIntoCollection()
    {
        $subj = ["as", "qw"];
        $t = new TypedCollectionTransformer();
        /** @var \ArrayObject $result */
        $result = $t->transform($subj, new TypedCollectionMetadata(null, false, "ArrayObject"), $this->getFakeTransformer());
        $this->assertTrue($result instanceof \ArrayObject);
        $this->assertSame($subj, $result->getArrayCopy());
    }

    public function testTransformFromCollection()
    {
        $subj = new \ArrayObject($source = ["as", "qw"]);
        $t = new TypedCollectionTransformer();
        $result = $t->transform($subj, new TypedCollectionMetadata(null), $this->getFakeTransformer());
        $this->assertSame($source, $result);
    }

    public function testTypedTransform()
    {
        $subj = ["12", "34"];
        $t = new TypedCollectionTransformer();
        $transformer = $this->getFakeTransformer(["transform"]);
        $transformer->expects($this->at(0))->method("transform")->with("12", "type")->will($this->returnValue("qw"));
        $transformer->expects($this->at(1))->method("transform")->with("34", "type")->will($this->returnValue("as"));
        $result = $t->transform($subj, new TypedCollectionMetadata("type"), $transformer);
        $this->assertSame(["qw", "as"], $result);
    }

    public function testCompileSetsResult()
    {
        $t = new TypedCollectionTransformer();

        $frame = $this->getMock('Bumblebee\Compilation\CompilationFrame', [], ["getInputData", "addStatement", "setResult"], '', false);
        $frame->expects($this->any())->method("getInputData")->will($this->returnValue(new Variable("input")));
        $frame->expects($this->any())->method("addStatement");
        $frame->expects($this->atLeastOnce())->method("setResult");

        $ctx = $this->getMock('Bumblebee\Compilation\CompilationContext', ["getCurrentFrame"], [new Variable("input"), new Variable("transformer")]);
        $ctx->expects($this->any())->method("getCurrentFrame")->will($this->returnValue($frame));

        $t->compile($ctx, new TypedCollectionMetadata(null), $this->getMock('Bumblebee\Compiler', [], [], "", false));
    }

    public function testValidation()
    {
        $t = new TypedCollectionTransformer();
        $ctx = $this->getMock('Bumblebee\Metadata\ValidationContext', ["validateLater"], [], "", false);
        $ctx->expects($this->once())->method("validateLater")->with("deferred_type");

        $errors = $t->validateMetadata($ctx, new TypedCollectionMetadata("deferred_type"));
        $this->assertCount(0, $errors);

        $errors = $t->validateMetadata($this->getMock('Bumblebee\Metadata\ValidationContext', [], [], "", false), new TypeMetadata(""));
        $this->assertCount(1, $errors);
        $this->assertSame('Bumblebee\TypeTransformer\TypedCollectionTransformer expects TypedCollectionMetadata,' .
            ' Bumblebee\Metadata\TypeMetadata given', $errors[0]->getMessage());
    }

}

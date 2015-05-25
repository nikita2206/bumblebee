<?php

namespace Bumblebee\Tests\Unit\TypeTransformer;

use Bumblebee\Compilation\Variable;
use Bumblebee\Metadata\NumberFormatMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\TypeTransformer\NumberFormatTransformer;

class NumberFormatTransformerTest extends \PHPUnit_Framework_TestCase
{

    public function testMetadataTypeValidation()
    {
        $t = new NumberFormatTransformer();

        $errors = $t->validateMetadata(new ValidationContext(), new TypeMetadata(""));
        $this->assertCount(1, $errors);
        $this->assertSame('Bumblebee\TypeTransformer\NumberFormatTransformer expects instance of' .
            ' NumberFormatMetadata, Bumblebee\Metadata\TypeMetadata given', $errors[0]->getMessage());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTransformMetadataCheck()
    {
        $t = new NumberFormatTransformer();

        $t->transform("1", new TypeMetadata(""), $this->getMock('Bumblebee\Transformer', [], [], "", false));
    }

    public function testTransform()
    {
        $t = new NumberFormatTransformer();
        $fakeTransformer = $this->getMock('Bumblebee\Transformer', [], [], "", false);

        $this->assertSame("1.00", $t->transform(1, new NumberFormatMetadata(2), $fakeTransformer));
        $this->assertSame("12,124.12", $t->transform(12124.123, new NumberFormatMetadata(2), $fakeTransformer));
        $this->assertSame("12 124,12", $t->transform(12124.123, new NumberFormatMetadata(2, ",", " "), $fakeTransformer));
    }

    public function testCompileSetsResult()
    {
        $t = new NumberFormatTransformer();

        $frame = $this->getMock('Bumblebee\Compilation\CompilationFrame', ["getInputData", "setResult"], [], "", false);
        $frame->expects($this->any())->method("getInputData")->will($this->returnValue(new Variable("inp")));
        $frame->expects($this->atLeastOnce())->method("setResult");

        $ctx = $this->getMock('Bumblebee\Compilation\CompilationContext', ["getCurrentFrame"], [], "", false);
        $ctx->expects($this->any())->method("getCurrentFrame")->will($this->returnValue($frame));

        $t->compile($ctx, new NumberFormatMetadata(), $this->getMock('Bumblebee\Compiler', [], [], "", false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCompileChecksMetadata()
    {
        $t = new NumberFormatTransformer();

        $t->compile($this->getMock('Bumblebee\Compilation\CompilationContext', [], [], "", false), new TypeMetadata(""),
            $this->getMock('Bumblebee\Compiler', [], [], "", false));
    }

}

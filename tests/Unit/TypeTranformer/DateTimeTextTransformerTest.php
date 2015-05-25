<?php

namespace Bumblebee\Tests\Unit\TypeTransformer;

use Bumblebee\Compilation\Variable;
use Bumblebee\Metadata\DateTimeMetadata;
use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\TypeTransformer\DateTimeTextTransformer;

class DateTimeTextTransformerTest extends \PHPUnit_Framework_TestCase
{

    public function testMetadataTypeValidation()
    {
        $t = new DateTimeTextTransformer();

        $errors = $t->validateMetadata(new ValidationContext(), new TypeMetadata(""));
        $this->assertCount(1, $errors);
        $this->assertSame('Bumblebee\TypeTransformer\DateTimeTextTransformer expects instance of' .
            ' DateTimeMetadata, Bumblebee\Metadata\TypeMetadata given', $errors[0]->getMessage());

        $errors = $t->validateMetadata(new ValidationContext(), new DateTimeMetadata(new \stdClass()));
        $this->assertCount(1, $errors);
        $this->assertSame('Date format is expected to be a string, object given', $errors[0]->getMessage());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testTransformInputDataCheck()
    {
        $t = new DateTimeTextTransformer();

        $t->transform("", new DateTimeMetadata(""), $this->getMock('Bumblebee\Transformer', [], [], '', false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTransformMetadataCheck()
    {
        $t = new DateTimeTextTransformer();

        $t->transform(new \DateTime(), new TypeMetadata(""), $this->getMock('Bumblebee\Transformer', [], [], '', false));
    }

    public function testTransform()
    {
        $fakeTransformer = $this->getMock('Bumblebee\Transformer', [], [], '', false);
        $t = new DateTimeTextTransformer();

        $dt = new \DateTime();
        $isoCurrentDate = $t->transform($dt, new DateTimeMetadata(\DateTime::ISO8601), $fakeTransformer);
        $this->assertSame($dt->format(\DateTime::ISO8601), $isoCurrentDate);

        $dt = new \DateTime("2015-02-12");
        $date = $t->transform($dt, new DateTimeMetadata("d/m/Y"), $fakeTransformer);
        $this->assertSame($dt->format("d/m/Y"), $date);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCompileChecksMetadata()
    {
        $t = new DateTimeTextTransformer();

        $t->compile($this->getMock('Bumblebee\Compilation\CompilationContext', [], [], "", false), new TypeMetadata(""),
            $this->getMock('Bumblebee\Compiler', [], [], "", false));
    }

    public function testCompileSetsResult()
    {
        $t = new DateTimeTextTransformer();

        $frame = $this->getMock('Bumblebee\Compilation\CompilationFrame', ["getInputData", "setResult"], [], "", false);
        $frame->expects($this->any())->method("getInputData")->will($this->returnValue(new Variable("inp")));
        $frame->expects($this->atLeastOnce())->method("setResult");

        $ctx = $this->getMock('Bumblebee\Compilation\CompilationContext', ["getCurrentFrame"], [], "", false);
        $ctx->expects($this->any())->method("getCurrentFrame")->will($this->returnValue($frame));
        $t->compile($ctx, new DateTimeMetadata("Y-m-d"), $this->getMock('Bumblebee\Compiler', [], [], "", false));
    }

}

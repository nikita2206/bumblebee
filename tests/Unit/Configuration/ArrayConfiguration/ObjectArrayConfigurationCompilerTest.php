<?php

namespace Unit\Configuration\ArrayConfiguration;

use Bumblebee\Configuration\ArrayConfiguration\ObjectArrayConfigurationCompiler;
use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata;
use Bumblebee\Metadata\ObjectArray\ObjectArrayElementMetadata;
use Bumblebee\Metadata\ObjectArray\ObjectArrayMetadata;

class ObjectArrayConfigurationCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testCommonCase()
    {
        $oaCompiler = new ObjectArrayConfigurationCompiler();
        $compiler = $this->getMock(ArrayConfigurationCompiler::class, ["chain"], [], "", false);
        $compiler->expects($this->once())->method("chain")->with(["typeBar", "typeFoo"])
            ->will($this->returnValue("chainedFooBar"));

        $compiled = $oaCompiler->compile(["elements" => [
            "foo" => "typeFoo(typeBar(getFoo()->prop->unwrap()))"
        ]], $compiler);

        $this->assertEquals(new ObjectArrayMetadata([
            new ObjectArrayElementMetadata("chainedFooBar", "foo", [
                new ObjectArrayAccessorMetadata("getFoo"),
                new ObjectArrayAccessorMetadata("prop", false),
                new ObjectArrayAccessorMetadata("unwrap")
            ])
        ]), $compiled);
    }
}

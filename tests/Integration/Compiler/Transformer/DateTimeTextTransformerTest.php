<?php

namespace Bumblebee\Tests\Integration\Compiler\Transformer;

use Bumblebee\Metadata\DateTimeMetadata;

class DateTimeTextTransformerTest extends TransformerCompilationTestCase
{

    public function testBasicCase()
    {
        $t = $this->generateTransformer("foo", [
            "foo" => new DateTimeMetadata(\DateTime::ISO8601)
        ]);
        $transformer = $this->getFakeTransformer();

        $dt = new \DateTime();
        $result = $t($dt, $transformer);

        $this->assertSame($dt->format(\DateTime::ISO8601), $result);

        if (class_exists('DateTimeImmutable', false)) {
            $dti = new \DateTimeImmutable();
            $result = $t($dti, $transformer);

            $this->assertSame($dti->format(\DateTime::ISO8601), $result);
        }
    }

}

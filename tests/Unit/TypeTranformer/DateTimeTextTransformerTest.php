<?php

namespace Bumblebee\Tests\Unit\TypeTransformer;

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

    public function test

}

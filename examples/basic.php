<?php

require __DIR__ . "/../vendor/autoload.php";

use Bumblebee\Metadata\ValidationContext,
    Bumblebee\Metadata\TypeMetadata,
    Bumblebee\Transformer,
    Bumblebee\TypeTransformer\TypeTransformer,
    Bumblebee\Metadata\ObjectArrayMetadata,
    Bumblebee\Metadata\ObjectArrayFieldMetadata;

class CustomTransformer implements TypeTransformer
{
    public function transform($data, TypeMetadata $metadata, Transformer $transformer)
    {
        return implode(", ", $data);
    }

    public function validateMetadata(ValidationContext $context, TypeMetadata $metadata)
    {
        return [];
    }
}

$typeProvider = new \Bumblebee\BasicTypeProvider([
    "first_type" => new ObjectArrayMetadata([
        new ObjectArrayFieldMetadata(null, "stuff", "getStuff", true),
        new ObjectArrayFieldMetadata("second_type", "foo", "foo", false)
    ]),
    "second_type" => new TypeMetadata("custom")
]);

$transformerProvider = new \Bumblebee\LocatorTransformerProvider([
    "object_array" => 'Bumblebee\TypeTransformer\ObjectArrayTransformer',
    "custom" => 'CustomTransformer'
]);

$transformer = new Transformer($typeProvider, $transformerProvider);

class FirstClass
{

    public $foo;

    public function getStuff()
    {
        return md5(mt_rand());
    }

}

$firstInstance = new FirstClass();
$firstInstance->foo = ["paper", "rock", "scissors"];

var_dump($transformer->transform($firstInstance, "first_type"));

<?php

require __DIR__ . "/../vendor/autoload.php";

use Bumblebee\Metadata\ValidationContext,
    Bumblebee\Metadata\TypeMetadata,
    Bumblebee\Transformer,
    Bumblebee\TypeTransformer\TypeTransformer,
    Bumblebee\Metadata\ObjectArrayMetadata,
    Bumblebee\Metadata\ObjectArrayFieldMetadata;

/**
 * This is how you can create your own custom transformers
 * It is later registered in LocatorTransformerProvider under the name 'custom'
 */
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

/**
 * TypeProvider should implement Bumblebee\TypeProvider interface, it is needed to get type metadata
 */
$typeProvider = new \Bumblebee\BasicTypeProvider([
    "first_type" => new ObjectArrayMetadata([
        new ObjectArrayFieldMetadata(null, "stuff", "getStuff", true),
        new ObjectArrayFieldMetadata("second_type", "foo", "foo", false)
    ]),
    "second_type" => new TypeMetadata("custom")
]);

/**
 * TransformerProvider is used to instances of TypeTransformers, you can implement your own
 * with any additional logic you want, just implement Bumblebee\TransformerProvider interface
 */
$transformerProvider = new \Bumblebee\LocatorTransformerProvider([
    "object_array" => 'Bumblebee\TypeTransformer\ObjectArrayTransformer',
    "custom" => 'CustomTransformer'
]);

$transformer = new Transformer($typeProvider, $transformerProvider);

/**
 * We will use this class to transform it to an array
 */
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

/**
 * In the end you'll get something like
 * array(2) {
 *   'stuff' =>
 *   string(32) "38f5b950bf27ceb8a80309370a86cd9c"
 *   'foo' =>
 *   string(21) "paper, rock, scissors"
 * }
 */
var_dump($transformer->transform($firstInstance, "first_type"));

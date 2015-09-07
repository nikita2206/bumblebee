<?php

/*
 * Let's create a custom trim transformer, it will take value and pass
 * it to a trim() function along with its second parameter (charlist)
 * which will be defined in metadata
 */

use Bumblebee\Metadata\TypeMetadata;
use Bumblebee\TypeTransformer\CompilableTypeTransformer;
use Bumblebee\Compilation\CompilationContext;
use Bumblebee\Compiler;
use Bumblebee\Transformer;
use Bumblebee\Metadata\ValidationContext;
use Bumblebee\Metadata\ValidationError;
use Bumblebee\Metadata\ObjectArray\ObjectArrayMetadata;
use Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata;
use Bumblebee\Metadata\ObjectArray\ObjectArrayElementMetadata;
use Bumblebee\Configuration\ArrayConfiguration\TransformerConfigurationCompiler;
use Bumblebee\Configuration\ArrayConfigurationCompiler;
use Bumblebee\Configuration\ArrayConfiguration\ObjectArrayConfigurationCompiler;
use Bumblebee\TypeTransformer\ObjectArrayTransformer;

require __DIR__ . "/../vendor/autoload.php";

class TrimMetadata extends TypeMetadata
{

    protected $charlist;

    public function __construct($charlist)
    {
        parent::__construct("trim_transformer");

        $this->charlist = $charlist;
    }

    public function getCharlist()
    {
        return $this->charlist;
    }

}

class TrimTransformer implements CompilableTypeTransformer
{
    public function transform($data, TypeMetadata $metadata, Transformer $transformer)
    {
        if ( ! $metadata instanceof TrimMetadata) {
            throw new \InvalidArgumentException();
        }

        return trim($data, $metadata->getCharlist());
    }

    public function validateMetadata(ValidationContext $context, TypeMetadata $metadata)
    {
        if ( ! $metadata instanceof TrimMetadata) {
            return [new ValidationError(sprintf("%s expects instance of TrimMetadata, %s given", __CLASS__, get_class($metadata)))];
        }

        return [];
    }

    public function compile(CompilationContext $ctx, TypeMetadata $metadata, Compiler $compiler)
    {
        if ( ! $metadata instanceof TrimMetadata) {
            throw new \InvalidArgumentException();
        }

        $inputData = $ctx->getCurrentFrame()->getInputData();
        $result = $ctx->callFunction($ctx->constValue("trim"), [
            $inputData,
            $ctx->compileTimeValue($metadata->getCharlist())
        ]);
        $ctx->getCurrentFrame()->setResult($result);
    }
}

/*
 * Now we can actually use it:
 */

$transformers = new \Bumblebee\LocatorTransformerProvider([
    "trim_transformer" => TrimTransformer::class,
    "object_array" => ObjectArrayTransformer::class
]);

$transformer = new Transformer(
    new \Bumblebee\BasicTypeProvider([
        "trim" => new TrimMetadata("\r\n\t \0"),
        "foo" => new ObjectArrayMetadata($fields = [
            new ObjectArrayElementMetadata($type = "trim", $name = "asd", $accessorChain = [
                new ObjectArrayAccessorMetadata("bar", false)
            ])
        ])
    ]),
    $transformers
);

$input = (object)["bar" => "  foo  "];

$transformed = $transformer->transform($input, "foo"); // trim($input->bar)
echo $transformed["asd"]; // outputs "foo" without spaces
echo PHP_EOL;

/*
 * Now we could have used configuration component in order
 * to make it more convenient. But we'll need to define
 * ConfigurationCompiler for TrimMetadata
 */

class TrimConfigurationCompiler implements TransformerConfigurationCompiler
{
    public function compile(array $configuration, ArrayConfigurationCompiler $compiler)
    {
        return new TrimMetadata($configuration["charlist"]);
    }
}

$configurationCompiler = new ArrayConfigurationCompiler([
    "trim" => new TrimConfigurationCompiler(),
    "object_array" => new ObjectArrayConfigurationCompiler()
]);

$metadata = $configurationCompiler->compile([
    "trim" => [
        "tran" => "trim",
        "charlist" => "\r\n\t \0"
    ],
    "foo" => [
        "tran" => "object_array",
        "elements" => ["asd" => "trim(bar)"]
    ]
]);

$transformer = new Transformer(new \Bumblebee\BasicTypeProvider($metadata), $transformers);

$newTransformed = $transformer->transform($input, "foo");
var_dump($newTransformed === $transformed); // bool(true) - they are identical

$compiler = new Compiler(new \Bumblebee\BasicTypeProvider($metadata), $transformers);
$functionSourceCode = $compiler->compile("foo");
/*
 * returns source code of anonymous function
 * its signature is callable(mixed $input, Transformer $transformer): mixed
 * it needs an instance of $transformer in case if some of the types were not actually
 * compilable, it will call $transformer->transform() then
 */

echo $functionSourceCode, PHP_EOL; // have a look at generated source code:
/*
 * function($input, $transformer) {
 *     return ['asd' => trim($input->bar, "\r\n\t \0"),];
 * }
 *
 * I reformatted this code a bit, to make it more readable, but you get the idea
 */

// let's get the actual function from source code
$fooTransformer = eval("return {$functionSourceCode};");

$newTransformed = $fooTransformer($input, $transformer);
var_dump($newTransformed === $transformed); // bool(true)

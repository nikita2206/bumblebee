<?php

require __DIR__ . "/../vendor/autoload.php";

use Bumblebee\Metadata\ValidationContext,
    Bumblebee\Metadata\TypeMetadata,
    Bumblebee\Transformer,
    Bumblebee\TypeTransformer\TypeTransformer,
    Bumblebee\Metadata\ObjectArray\ObjectArrayMetadata,
    Bumblebee\Metadata\ObjectArray\ObjectArrayElementMetadata;


class Ad
{

    public $name;

    public $bid;

    public $group;

}

class Bid
{

    public $amount;

    public $paymentType;

}

class Group
{

    public $preview, $members, $type;

}


$confCompiler = new \Bumblebee\Configuration\ArrayConfigurationCompiler([
    "array_to_object" => new \Bumblebee\Configuration\ArrayConfiguration\ArrayToObjectConfigurationCompiler(),
    "function" => new \Bumblebee\Configuration\ArrayConfiguration\FunctionConfigurationCompiler()
]);

$md = $confCompiler->compile([
    "ad" => [
        "tran" => "array_to_object",
        "class" => "Ad",
        "settings" => [
            "name" => "trim(string(?name))",
            "bid" => [
                "tran" => "array_to_object",
                "props" => [
                    "class" => "Bid",
                    "settings" => [
                        "amount" => "?bid",
                        "paymentType" => "trim(string(?paymentType))"]]],
            "group" => [
                "tran" => "array_to_object",
                "props" => [
                    "class" => Group::class,
                    "settings" => [
                        "preview" => "?group_preview",
                        "members" => "?group_members",
                        "type" => "?group_type"]]]]],
    "string" => [
        "tran" => "function",
        "func" => "strval"],
    "trim" => [
        "tran" => "function",
        "func" => "trim"]]);

$transformer = new Transformer(
    $types = new \Bumblebee\BasicTypeProvider($md),
    $transformers = new \Bumblebee\LocatorTransformerProvider([
        "array_to_object" => \Bumblebee\TypeTransformer\ArrayToObjectTransformer::class,
        "function" => \Bumblebee\TypeTransformer\FunctionTransformer::class,
        "chain" => \Bumblebee\TypeTransformer\ChainTransformer::class]));

$objects = $transformer->transform([
    "name" => "  Qwe ",
    "bid" => 123,
    "paymentType" => "asd"
], "ad");

var_dump($objects);

$compiler = new \Bumblebee\Compiler($types, $transformers);
echo $genTransformerCode = $compiler->compile("ad");

/** @var \Closure $genTransformer */
$genTransformer = eval("return {$genTransformerCode};");

$data = [
    "name" => "Qwe",
    "bid" => 123,
    "paymentType" => "asd"];

$t1 = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    $transformer->transform($data, "ad");
}
$t1 = microtime(true) - $t1;

$t2 = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    $genTransformer($data, $transformer);
}
$t2 = microtime(true) - $t2;

echo $t1 * 1000, "\n";
echo $t2 * 1000, "\n";

__halt_compiler();

/**
 * Lets say we have these domain classes
 */
class BlogPost
{

    protected $title;

    protected $shortDescription;

    protected $comments;

    protected $postedAt;

    protected $tags;

    public function __construct($title, $shortDesc, DateTime $postedAt, $comments = [])
    {
        $this->title = $title;
        $this->shortDescription = $shortDesc;
        $this->postedAt = $postedAt;
        $this->comments = $comments;

        $this->tags = array_filter(array_unique(explode(" ", $title . " " . $shortDesc)), function ($tag) { return strlen($tag) > 4; });
        sort($this->tags);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    public function getPostedAt()
    {
        return $this->postedAt;
    }

    public function getTags()
    {
        return $this->tags;
    }

}

class Comment
{

    protected $content;

    protected $children;

    public function __construct($content, $children)
    {
        $this->content = $content;
        $this->children = $children;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getChildren()
    {
        return $this->children;
    }

}


/**
 * This is how you can create your own custom transformers
 * It is later registered in LocatorTransformerProvider under the name 'tags_transformer' and used in 'tags' type
 */
class TagsTransformer implements TypeTransformer
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
 * Here we pass all the metadata needed for transforming our domain objects into arrays that would look like this:
 * array(
 *  "title" => ...,
 *  "short_description" => ...,
 *  "posted_at" => ...,
 *  "tags" => ..., (we combine tags with implode())
 *  "comments" => array(
 *   array("content" => ..., "children" => array(array("content" => ..., "children" => ...))),
 *   ...
 *  )
 */
$typeProvider = new \Bumblebee\BasicTypeProvider([
    "blog_post" => new ObjectArrayMetadata([
        new ObjectArrayElementMetadata(null, "title", [new \Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata("getTitle")]),
        new ObjectArrayElementMetadata(null, "short_description", [new \Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata("getShortDescription")]),
        new ObjectArrayElementMetadata("datetime_iso8601", "posted_at", [new \Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata("getPostedAt")]),
        new ObjectArrayElementMetadata("comments", "comments", [new \Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata("getComments")]),
        new ObjectArrayElementMetadata("tags", "tags", [new \Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata("getTags")])
    ]),
    "comments" => new \Bumblebee\Metadata\TypedCollectionMetadata("comment"),
    "comment" => new ObjectArrayMetadata([
        new ObjectArrayElementMetadata(null, "content", [new \Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata("getContent")]),
        new ObjectArrayElementMetadata("comments", "children", [new \Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata("getChildren")])
    ]),
    "tags" => new TypeMetadata("tags_transformer"),
    "datetime_iso8601" => new \Bumblebee\Metadata\DateTimeMetadata(DATE_ISO8601)
]);

/**
 * TransformerProvider is used to instances of TypeTransformers, you can implement your own
 * with any additional logic you want, just implement Bumblebee\TransformerProvider interface
 */
$transformerProvider = new \Bumblebee\LocatorTransformerProvider([
    "object_array" => 'Bumblebee\TypeTransformer\ObjectArrayTransformer',
    "datetime_text" => 'Bumblebee\TypeTransformer\DateTimeTextTransformer',
    "typed_collection" => 'Bumblebee\TypeTransformer\TypedCollectionTransformer',
    "tags_transformer" => 'TagsTransformer'
]);

$transformer = new Transformer($typeProvider, $transformerProvider);

$comment1 = new Comment("Wow fascinating!", [new Comment("I know right!", [new Comment("No you don't", [])])]);
$comment2 = new Comment("Meh..", []);

$post = new BlogPost("You Won't Believe What Scientists Have Found Out", "Lots of stuff, really", new DateTime("2014-05-12"), [$comment1, $comment2]);

$postArray = $transformer->transform($post, "blog_post");

var_dump($postArray);

$input = [
    "name" => "Qwe",
    "bid" => 1234,
    "paymentType" => "cpm"
];


$typeProvider = new \Bumblebee\BasicTypeProvider([
    "ad" => new \Bumblebee\Metadata\ArrayToObject\ArrayToObjectMetadata(Ad::class, [], [
        new \Bumblebee\Metadata\ArrayToObject\ArrayToObjectSettingMetadata("name", [
            new \Bumblebee\Metadata\ArrayToObject\ArrayToObjectArgumentMetadata(null, ["name"])
        ], false),
        new \Bumblebee\Metadata\ArrayToObject\ArrayToObjectSettingMetadata("bid", [
            new \Bumblebee\Metadata\ArrayToObject\ArrayToObjectArgumentMetadata("bid", [])
        ], false)
    ]),
    "bid" => new \Bumblebee\Metadata\ArrayToObject\ArrayToObjectMetadata(Bid::class, [], [
        new \Bumblebee\Metadata\ArrayToObject\ArrayToObjectSettingMetadata("amount", [
            new \Bumblebee\Metadata\ArrayToObject\ArrayToObjectArgumentMetadata(null, ["bid"])
        ], false),
        new \Bumblebee\Metadata\ArrayToObject\ArrayToObjectSettingMetadata("paymentType", [
            new \Bumblebee\Metadata\ArrayToObject\ArrayToObjectArgumentMetadata(null, ["paymentType"])
        ], false)
    ]),
    "ad_to_array" => new ObjectArrayMetadata([
        new ObjectArrayElementMetadata(null, "name", [new \Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata("name", false)]),
        new ObjectArrayElementMetadata(null, "bid", [new \Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata("bid", false), new \Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata("amount", false)]),
        new ObjectArrayElementMetadata(null, "paymentType", [new \Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata("bid", false), new \Bumblebee\Metadata\ObjectArray\ObjectArrayAccessorMetadata("paymentType", false)])
    ])
]);


$transformer = new Transformer($typeProvider, $transformerProvider);
$compiler = new \Bumblebee\Compiler($typeProvider, $transformerProvider);

$fnCode = $compiler->compile("ad");

$fn = eval("return {$fnCode};");

var_dump($fn($input, $transformer));

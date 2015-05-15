<?php

require __DIR__ . "/../vendor/autoload.php";

use Bumblebee\Metadata\ValidationContext,
    Bumblebee\Metadata\TypeMetadata,
    Bumblebee\Transformer,
    Bumblebee\TypeTransformer\TypeTransformer,
    Bumblebee\Metadata\ObjectArrayMetadata,
    Bumblebee\Metadata\ObjectArrayFieldMetadata;


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

    public function __construct($title, $shortDesc, DateTime $postedAt, $comments)
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
        new ObjectArrayFieldMetadata(null, "title", "getTitle", true),
        new ObjectArrayFieldMetadata(null, "short_description", "getShortDescription", true),
        new ObjectArrayFieldMetadata("datetime_iso8601", "posted_at", "getPostedAt", true),
        new ObjectArrayFieldMetadata("comments", "comments", "getComments", true),
        new ObjectArrayFieldMetadata("tags", "tags", "getTags", true)
    ]),
    "comments" => new \Bumblebee\Metadata\TypedCollectionMetadata("comment"),
    "comment" => new ObjectArrayMetadata([
        new ObjectArrayFieldMetadata(null, "content", "getContent", true),
        new ObjectArrayFieldMetadata("comments", "children", "getChildren", true)
    ]),
    "tags" => new TypeMetadata("tags_transformer"),
    "datetime_iso8601" => new \Bumblebee\Metadata\DateTimeMetadata(DATE_ISO8601),
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


$errors = $transformer->validateTypes();

echo "Errors:", PHP_EOL;
var_dump($errors);


$comment1 = new Comment("Wow fascinating!", [new Comment("I know right!", [new Comment("No you don't", [])])]);
$comment2 = new Comment("Meh..", []);

$post = new BlogPost("You Won't Believe What Scientists Have Found Out", "Lots of stuff, really", new DateTime("2014-05-12"), [$comment1, $comment2]);

print_r($transformer->transform($post, "blog_post"));

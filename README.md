Bumblebee
=========

[![Build Status](https://travis-ci.org/nikita2206/bumblebee.svg?branch=master)](https://travis-ci.org/nikita2206/bumblebee)

What is this?
-------------

Bumblebee is a library that helps you transform data from one structure to another,
be it an object-to-array mapping, array-to-object mapping or anything-to-anything.

Where can this be useful?
-------------------------

Data transformation (aka data marshalling) is one of the main things that happen in systems
of different complexity.
It is happening when you are mapping your data-objects to a representation that is compatible
with your view layer, it is also happening when you are mapping database results on objects
or user input on your data-objects. You can also use it for API responses, for example
you can conditionally generate [Option type](http://en.wikipedia.org/wiki/Option_type)
responses out of Soap's stdClass instances (or from plain arrays).

How do I use it?
----------------

This library is divided into four pieces that work together:

First one being *configuration compiler*. This component allows you to write transformation
   rules in a more expressive way than if you would write them manually using Metadata component
   directly. It supports yaml configuration via ArrayConfigurationCompiler and XML via
   XmlConfigurationCompiler (WIP). Configuration compiler takes appropriate input and gives
   metadata as its output.

Now *metadata*, this is another part of Bumblebee, it's used for configuring transformation
   rules (aka *types*). Each type has to have a unique name and can be reused in other types
   if their transformer allow you to do so (e.g. each element in ArrayToObject metadata
   can be passed through another transformation type). Metadata is a necessary component
   unlike configuration compiler, it's a direct dependency of *transformer* itself.

Runtime transformer (`Bumblebee\Transformer`) is a third piece. You can feed it your
   type definitions and transformers which they relate to and then via `transform()` method
   you will transform your data according to the type you choose.

There's a better way to transform your data though, it's through the use of `Bumblebee\Compiler`.
   There are transformers that can be compiled into straightforward and simple PHP code,
   at this moment all of them can. This code will then be able to transform your data much
   faster than runtime transformer can and with less memory overhead.
   Let's say you're trying to map array on object, there are ten elements in an array
   and each of them needs to be processed through another transformation type (let's use
   number_format transformation for it) and then set directly to the object as a public field.
   You can look at `Bumblebee\TypeTransformer\ArrayToObjectTransformer#transform()` to see
   how much stuff happens when you do it. And for each field it will call back into
   `Bumblebee\Transformer` for it to call `NumberTransformer#transform`. This takes quite
   a lot of resources and if you have some big amount of records to process it can get quite slow,
   you can take JMSSerializer as an example.
   Now if we compile it we'll get a function that would look something like this:

```php
function ($input, Bumblebee\Transformer $transformer) {
    $object = new ClassName();
    $object->foo = number_format($input["foo"]);
    ...
    $object->bar = number_format($input["bar"]);
    return $object;
};
```

Customizing transformers
------------------------

You can customize existing transformers or write new ones from scratch. If your new transformer
requires any rules for it to work you'll need to create new Metadata class for it,
it has to extend `Bumblebee\Metadata\TypeMetadata`. Then of course you'll need
to create TypeTransformer class, it has to implement
`Bumblebee\TypeTransformer\TypeTransformer` interface. However if you want to create
compilable TypeTransformer then you'd need to implement
`Bumblebee\TypeTransformer\CompilableTypeTransformer`. For the reference on
how to write compilable transformers you can look at `examples/` directory
or you can look at some already written transformers in `src/TypeTransformer/`.
And finally you'll need to write configuration compilers if you use array or xml
configuration.

Why shouldn't I just write transformers manually?
-------------------------------------------------

Well, because writing anything manually sucks. If you write transformations manually
there's always a chance for a mistake. However a scope of possible mistakes is narrowed
here because transformers are well-tested with unit and integration tests, and because
things that can be validated are validated at build-time.
Also because Bumblebee has a declarative syntax for transformation rules which can
be checked and validated if you ask it to, so that
you don't run into strange bugs. And finally Bumblebee transformers can be compiled
according to your transformation rules as optimal as you would write it.
But if you would be writing transformers manually you would probably go for readability
and lose some precious milliseconds because of the couple of extra call-stack frames where
Bumblebee wouldn't ever do that.
So in the end you get optimal and compile-time (build-time) checked code at the
same time without harming readability.

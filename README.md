Bumblebee
=========

What is this?
-------------

Bumblebee was a name of one of the main characters from Transformers movie. Bumblebee is a library that helps you
to transform data from one structure to another, be it an object-to-array mapping, array-to-object mapping, or even
array-to-array mapping from one structure to another.

Where can this be useful?
-------------------------

Data transformation is one of the main things that happen in systems of different complexity. It is happening
when you are mapping your data-objects to a representation that is compatible with your view layer, it is also happening
when you are mapping database results on objects or user input on your data-objects. You can also use it for API
responses, for example you can conditionally generate [Option type](http://en.wikipedia.org/wiki/Option_type) responses
out of Soap's stdClass instances (or from plain arrays).

How do I use it?
----------------

TODO

How does it work?
-----------------

TODO

Why shouldn't I just write transformers manually?
_________________________________________________

Well, because writing anything manually sucks. Also because Bumblebee has a declarative syntax for transforming rules
which can be checked and validated if you ask it to so you don't run into strange bugs. And finally Bumblebee
transformers can be compiled according to your transformation rules as optimal as you would write it, 
but if you would be writing a transformers manually you would probably go for readability and lose some
precious milliseconds because of couple of extra call-stack frames where Bumblebee wouldn't ever do it.
So in the end you would get optimal and compile-time (build-time) checked code at the same time without harming readability.

What's still missing?
_____________________

I'm in the process of writing tests right now so this is one of the things that's still not here yet.
There will be also demand in some TypeTransformers like NullConditionalTransformer (the one that would apply type
transformation to your value if it's not null) or even more broad ConditionalTransformer (this one would be much harder
to implement). And in the end we need configuration layer that wouldn't suck, as the current one (metadata) is pretty
powerful and extendable but it needs a nice wrapper in order to be able to write configuration in XML or YAML
that would look more readable.

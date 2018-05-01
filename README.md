# McValidator(PHP) [![Build Status](https://img.shields.io/travis/mcvalidator/mcvalidator-php.svg)](https://travis-ci.org/mcvalidator/mcvalidator-php) [![Latest Stable Version](https://img.shields.io/packagist/v/mcvalidator/mcvalidator-php.svg)](https://packagist.org/packages/mcvalidator/mcvalidator-php) [![Total Downloads](https://img.shields.io/packagist/dt/mcvalidator/mcvalidator-php.svg)](https://packagist.org/packages/mcvalidator/mcvalidator-php) [![Latest Unstable Version](https://img.shields.io/packagist/vpre/mcvalidator/mcvalidator-php.svg)](https://packagist.org/packages/mcvalidator/mcvalidator-php) [![License](https://img.shields.io/github/license/mcvalidator/mcvalidator-php.svg)](https://github.com/mcvalidator/mcvalidator-php/blob/master/LICENSE)

It's always [undefined index] somewhere in PHP.

McValidator is written to provide a way to validate and sanitize data

Example 1:
```php
use McValidator as MV;

// Builder is important because we can chain validator with them without
// too much work.
$builder = MV\valid('rule/is-string');

// Build the pipe
$pipe = $builder->build();

// Pump the value through the pipe
// Result contains the value and also informations about
// the runtime, such as errors and messages
$result = $pipe->pump(10);

// Gets the runtime state
$state = $result->getState();

// Gets the message of the head(first item) of errors
echo $state->getErrors()->head()->getMessage(); // Value is not a string

// We need more!
$builder2 = MV\shape_of([
    'a' => $builder
]);

$pipe2 = $builder2->build();

$result2 = $pipe2->pump(dict([
    'a' => 10
]));

// Gets the runtime state
$state2 = $result2->getState();

// Gets the message of the head(first item) of errors
echo $state2->getErrors()->head()->getMessage(); // outputs `Value is not a string`

// Gets the field path of the error!
echo $state2->getErrors()->head()->getStringPath('/' // <-- separator); // outputs `$/a`
// `$` means root of path 
```

You should be thinking now: this s\*\*t s\*\*ks you loser, it's way too verbose than j**a.
And we know, McValidator is not intended to be "simple" and "not verbose", but intended to provide ways to:
- **write validators** and also provide runtime information
- **track errors** without making you lose your mind asking what the freaking freak is happening when
you just threw a freaking exception from nowhere
- **no exception will be thrown** in runtime, except on structure configuration
- **value history**, yeah, every value has an history of values inside which get updated once every mutation, which is
in development
- **structure serialization**, this s\*\*t is good for caching, we can also cache closure, why the f\*\*k not? It's PHP.
This is also good because building validator structure may be expensive, if you cache the validator you can use
it without building on every request(it's PHP).
- **write once, validate everywhere**, the main intention on that library is to focus on provide a way to
write a validator and use it on JavaScript(web, node), Python, Ruby, etc., we have a Manifesto which is being
written to define the McValidator's implementation pattern.
- **we support YAML**, you can build your validator only using YAML, and this will make you productive,
because you can write a single YAML which will be valid in any other implementation of McValidator.
- **we respect data structures**, McValidator works together with [Heterogeny](https://github.com/heterogeny/heterogeny-php)
on PHP, thus respecting the way data should be, arrays being arrays and dictionaries being dictionaries, thus making
developing less confusing at least in PHP. **We will not support at any point any way to write a code that
will mix those structures, do not open an issue or PR about it**.
- You can validate on the depth of hell, McValidator provides a way to track errors even on nested structures, and when
we say deep, it's deep. An example of error path: 
`a/0/b/1/c`, which means that an error(happened on field `c` , of element `1`, of field `b`, of element `0`, of field `a`),
this is possible because McValidator is about chains and recursiveness, there's hardcore or such thing in our code.
- Immutability is our focus, sanitization or validation will not mutate data that is not on it's reach

### Value's type table

|         | `NonExistentValue` | `ExplicitNonExistentValue` | `InvalidValue` |
| ---     | ---                | ---                        | ---            |
| isValid | `false`            | `true`                     | `false`        | 
| exists  | `false`            | `false`                    | `true`         |


- `NonExistentValue`: a value that was not sent and does not exists, example: a key configured for a shape that was not "pumped"
- `ExplicitNonExistentValue`: a value that was sent explicitly `null`, example: a key configured for a shape that was "pumped" `null`
- `InvalidValue`: an invalid value which source is sections, example: a value that was sent and reported invalid in sections

__Performance is not guaranteed at this point.__
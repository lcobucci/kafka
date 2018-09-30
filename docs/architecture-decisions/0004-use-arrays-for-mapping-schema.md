# 4. Use arrays for mapping schema

Date: 2018-09-02

## Status

Accepted

## Context

The Java implementation of the protocol uses `final static` properties to define
the schema for requests and responses. These are defined using objects, making
it easy to support multiple versions for each API call.

PHP doesn't have this feature so we need decide how to solve this.

## Decision

We're still going to use objects to manipulate data and write/read content
to/from Kafka, however it would be simpler to use arrays in constants of each
request/response class.

Each field would be an array item, the key would be the field name and the value
would be the field type (or another array for more complex configuration).

The mapping would like this:

```php
use Lcobucci\Kafka\Protocol\Type;

final class DoSomethingRequest
{
    private const SCHEMAS = [
        [
            'error_code'   => Type\Int16::class,
            'api_versions' => [
                'type'     => Type\ArrayOf::class,
                'nullable' => false, // optional, default = false
                'items'    => [ // just type name if items don't have complex structure
                    'api_key'     => Type\Int16::class,
                    'min_version' => Type\Int16::class,
                    'max_version' => Type\Int16::class,
                ],
            ],
        ],
    ];
}
```

## Consequences

We'd need to create something to build the schema from an array, which allow us
to reuse instances of simple types.

A minor drawback is that the structure of the array should be validated in order
to create the correct schema, but since is an internal process there's no real
reason to worry about it.


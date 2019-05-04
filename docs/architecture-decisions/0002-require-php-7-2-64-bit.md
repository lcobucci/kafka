# 2. Require PHP 7.2 (64-bit)

Date: 2018-08-29

## Status

Superceded by [7. Require latest stable PHP version](0007-require-latest-stable-php-version.md)

## Context

Deciding which minimum PHP version to require is quite important because it
might restrict people from using the library, at the same time supporting many
versions increases the cost to maintain the library.

## Decision

For the first releases of this library we'll be requiring PHP 7.2+ (64-bit build
only).

The main motivation for only allowing it to be installed in 64-bit systems is
that Kafka's protocol uses signed longs (64-bit integers) for some fields, which
can't be achieve in 32-bit systems.

I believe that libraries' maintainers also have the role to help the evolution
of the PHP ecosystem, which makes me want to require a more up-to-date version
of the language.

## Consequences

This decision shouldn't be a general problem, considering that Kafka is a
relatively new tool in our stack and it doesn't make much sense to me to want to
use it with an old version of PHP.


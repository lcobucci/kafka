# 7. Require latest stable PHP version

Date: 2019-05-04

## Status

Accepted

Supercedes [2. Require PHP 7.2 (64-bit)](0002-require-php-7-2-64-bit.md)

## Context

We've previously decided to require PHP 7.2, however many months have passed and
PHP 7.3 is quite stable nowadays.

## Decision

Bump up requirement to latest stable PHP version (7.3 at the moment).

## Consequences

Not many people should be affected here, based on the very same argument used on
the previous ADR.

This is also a "political" view of this matter, since we're kind of requiring
people to always keep their stack up-to-date. Even when features available in
newer versions are not used, this decision allows the maintainers of this
library to be able to use them when necessary.

People are 100% free to fork the repository and maintain multiple PHP versions
if they need/want.


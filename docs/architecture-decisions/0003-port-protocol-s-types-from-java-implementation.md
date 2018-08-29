# 3. Port protocol's types from Java implementation

Date: 2018-08-29

## Status

Accepted

## Context

Kafka's protocol defines some primitive types which are used to send requests
and parse responses: https://kafka.apache.org/protocol#protocol_types

Providing an easy way to define the schema of the requests and responses is
quite critical to make this library extensible enough.

## Decision

We've decided to basically port the Java implementation to the PHP world,
because it was very well written and it simplifies things by a lot.

Some minor things obviously have to be adapted and for now we'll leave some
types to the upcoming releases - just because they aren't need to implement the
messages we're planning to provide at the moment.

## Consequences

The implementation of the schema of the requests and responses will be much
easier and reliable. The fact that some types will be skipped don't really
interfere on the overall functionality of the library.


# 5. Require Kafka v0.11

Date: 2019-04-11

## Status

Accepted

## Context

Supporting multiple Kafka versions is something quite trivial due to the way the
protocol was designed. With the [`ApiVersions`](https://kafka.apache.org/protocol#The_Messages_ApiVersions)
message, clients are able to retrieve the minimum and maximum supported version
for all API messages in a broker.

That is critical part in the connection flow and is suggested to be done right
after the connection has been established, however such message is only
available as of v0.10.

In Kafka v0.11, a new record batch format was introduced. This format offers
better metadata control, headers, and support for transactions.

More info:

 - https://cwiki.apache.org/confluence/display/KAFKA/KIP-35+-+Retrieving+protocol+version
 - https://kafka.apache.org/documentation/#messages

## Decision

This library will only be usable and guaranteed to be fully working with Kafka
v0.11+, although it's highly recommended always use the latest version
available (v2.2.0 at the moment).

## Consequences

We might restrict a few people, however this will make the library a bit more
reliable.

The implementation of how records are transported will also get quite
simplified, since only one format will be used.


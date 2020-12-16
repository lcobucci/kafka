# PHP Kafka client

[![Total Downloads]](https://packagist.org/packages/lcobucci/kafka)
[![Latest Stable Version]](https://packagist.org/packages/lcobucci/kafka)
[![Unstable Version]](https://packagist.org/packages/lcobucci/kafka)


[![Build Status]](https://github.com/lcobucci/kafka/actions?query=workflow%3A%22PHPUnit%20Tests%22+branch%3Amaster)
[![Code Coverage]](https://codecov.io/gh/lcobucci/kafka)

A library to allow people to communicate to Kafka using plain PHP, compatible with
Kafka **v0.11+** (due to the way the [protocol works](https://kafka.apache.org/protocol)).

## Installation

Package is available on [Packagist], you can install it using [Composer].

```shell
composer require lcobucci/kafka
```

### Dependencies

- PHP 7.4+ (**64-bit only**)

## Documentation

### Architectural decisions

This project uses [ADRs] to record all architectural decisions.
The summary can be found [here](docs/architecture-decisions/README.md).

## License

MIT, see [LICENSE].

[ADRs]: https://github.com/npryce/adr-tools
[Total Downloads]: https://img.shields.io/packagist/dt/lcobucci/kafka.svg?style=flat-square
[Latest Stable Version]: https://img.shields.io/packagist/v/lcobucci/kafka.svg?style=flat-square
[Unstable Version]: https://img.shields.io/packagist/vpre/lcobucci/kafka.svg?style=flat-square
[Build Status]: https://img.shields.io/github/workflow/status/lcobucci/kafka/PHPUnit%20tests/master?style=flat-square
[Code Coverage]: https://codecov.io/gh/lcobucci/kafka/branch/master/graph/badge.svg
[Packagist]: http://packagist.org/packages/lcobucci/kafka
[Composer]: http://getcomposer.org
[LICENSE]: LICENSE


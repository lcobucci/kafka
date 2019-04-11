# PHP Kafka client

[![Total Downloads](https://img.shields.io/packagist/dt/lcobucci/kafka.svg?style=flat-square)](https://packagist.org/packages/lcobucci/kafka)
[![Latest Stable Version](https://img.shields.io/packagist/v/lcobucci/kafka.svg?style=flat-square)](https://packagist.org/packages/lcobucci/kafka)
[![Unstable Version](https://img.shields.io/packagist/vpre/lcobucci/kafka.svg?style=flat-square)](https://packagist.org/packages/lcobucci/kafka)

![Branch master](https://img.shields.io/badge/branch-master-brightgreen.svg?style=flat-square)
[![Build Status](https://img.shields.io/travis/lcobucci/kafka/master.svg?style=flat-square)](https://travis-ci.org/lcobucci/kafka)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/lcobucci/kafka/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/lcobucci/kafka/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/lcobucci/kafka/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/lcobucci/kafka/?branch=master)

A library to allow people to communicate to Kafka using plain PHP, compatible with
Kafka **v0.11+** (due to the way the [protocol works](https://kafka.apache.org/protocol)).

## Installation

Package is available on [Packagist](https://packagist.org/packages/lcobucci/kafka),
you can install it using [Composer](https://getcomposer.org).

```shell
composer require lcobucci/kafka
```

### Dependencies

- PHP 7.2+ (**64-bit only**)

## Documentation

### Architectural decisions

This project uses [ADRs](https://github.com/npryce/adr-tools) to record all 
architectural decisions.

The summary can be found [here](docs/architecture-decisions/README.md).

## License

MIT, see [LICENSE file](LICENSE).

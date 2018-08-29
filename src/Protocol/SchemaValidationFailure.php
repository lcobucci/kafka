<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol;

use Lcobucci\Kafka\Exception;
use RuntimeException;
use function gettype;
use function sprintf;

/**
 * Thrown if the protocol schema validation fails while creating requests.
 */
final class SchemaValidationFailure extends RuntimeException implements Exception
{
}

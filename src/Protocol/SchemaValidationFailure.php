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
    public static function nullValue(string $expectedType): self
    {
        return new self(sprintf('Provided value is null, expected type: %s', $expectedType));
    }

    public static function incorrectType(mixed $data, string $expectedType): self
    {
        return new self(
            sprintf('%s(%s) does not have expected type: %s', gettype($data), $data, $expectedType),
        );
    }

    public static function incorrectClass(object $data, string $expectedClass): self
    {
        return new self(
            sprintf('Object (%s) is not an instance of: %s', $data::class, $expectedClass),
        );
    }

    public static function incorrectRange(int $data, int $lowerBound, int $upperBound): self
    {
        return new self(
            sprintf('%d is not between expected range: [%d, %d]', $data, $lowerBound, $upperBound),
        );
    }

    public static function incorrectLength(int $length, int $maxLength): self
    {
        return new self(
            sprintf('String length (%d) is larger than the maximum length (%d)', $length, $maxLength),
        );
    }

    public static function missingField(string $name): self
    {
        return new self(
            sprintf('Field "%s" missing from given structure', $name),
        );
    }

    public static function invalidValueForField(string $name, SchemaValidationFailure $failure): self
    {
        return new self(
            message: sprintf('Invalid value for field "%s": %s', $name, $failure->getMessage()),
            previous: $failure,
        );
    }
}

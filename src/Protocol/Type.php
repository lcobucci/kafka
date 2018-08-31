<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol;

use function strlen;

/**
 * Base interface for any serializable type
 */
abstract class Type
{
    /**
     * Writes given data to the message
     *
     * @param mixed $data
     *
     * @throws NotEnoughBytesAllocated When size of given value is bigger than the remaining allocated bytes.
     */
    abstract public function write($data, Message $message): void;

    /**
     * Returns content from the message
     *
     * @return mixed
     *
     * @throws NotEnoughBytesAllocated When trying to read a content bigger than the remaining allocated bytes.
     */
    abstract public function read(Message $message);

    /**
     * Returns the number of bytes necessary for given value (so that data can be allocated properly)
     *
     * @param mixed $data
     */
    abstract public function sizeOf($data): int;

    /**
     * Validates given value according to type's rules
     *
     * @param mixed $data
     */
    abstract public function validate($data): void;

    /**
     * Returns if the current type allows null values
     */
    public function isNullable(): bool
    {
        return false;
    }

    /**
     * Ensures that given value is not null
     *
     * @param mixed $data
     *
     * @throws SchemaValidationFailure
     */
    final protected function guardAgainstNull($data, string $expectedType): void
    {
        if ($data === null) {
            throw SchemaValidationFailure::nullValue($expectedType);
        }
    }

    /**
     * Ensures that given value matches expected type
     *
     * @param mixed $data
     *
     * @throws SchemaValidationFailure
     */
    final protected function guardType($data, string $expectType, callable $validator): void
    {
        if ($data !== null && ! $validator($data)) {
            throw SchemaValidationFailure::incorrectType($data, $expectType);
        }
    }

    /**
     * Ensures that given value is an instance of expected class
     *
     * @throws SchemaValidationFailure
     */
    final protected function guardClass(?object $data, string $expectedClass): void
    {
        if ($data === null) {
            return;
        }

        if (! $data instanceof $expectedClass) {
            throw SchemaValidationFailure::incorrectClass($data, $expectedClass);
        }
    }

    /**
     * Ensures that given value is between the expected range
     *
     * @throws SchemaValidationFailure
     */
    final protected function guardRange(int $data, int $lowerBound, int $upperBound): void
    {
        if ($data < $lowerBound || $data > $upperBound) {
            throw SchemaValidationFailure::incorrectRange($data, $lowerBound, $upperBound);
        }
    }

    /**
     * Ensures that given value's length is not larger than expected maximum length
     *
     * @throws SchemaValidationFailure
     */
    final protected function guardLength(?string $data, int $maxLength): void
    {
        if ($data === null) {
            return;
        }

        $length = strlen($data);

        if ($length > $maxLength) {
            throw SchemaValidationFailure::incorrectLength($length, $maxLength);
        }
    }
}

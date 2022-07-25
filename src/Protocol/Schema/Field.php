<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Schema;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type;

/**
 * Represents a field of a message
 */
final class Field
{
    public function __construct(private string $name, private Type $type)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * Writes content to the message using field type
     *
     * @param mixed[] $structure
     *
     * @throws SchemaValidationFailure When field is not nullable and missing from the structure.
     * @throws NotEnoughBytesAllocated When size of given value is bigger than the remaining allocated bytes.
     */
    public function writeTo(array $structure, Buffer $buffer): void
    {
        $this->type->write($this->extractValue($structure), $buffer);
    }

    /**
     * Reads content from message using field type
     *
     * @throws NotEnoughBytesAllocated When trying to read a content bigger than the remaining allocated bytes.
     */
    public function readFrom(Buffer $buffer): mixed
    {
        return $this->type->read($buffer);
    }

    /**
     * Returns the number of bytes necessary for this field
     *
     * @param mixed[] $structure
     *
     * @throws SchemaValidationFailure When field is not nullable and missing from the structure.
     */
    public function sizeOf(array $structure): int
    {
        return $this->type->sizeOf($this->extractValue($structure));
    }

    /**
     * Ensures that given data is valid
     *
     * @param array<string, mixed> $structure
     *
     * @throws SchemaValidationFailure When field is not nullable and missing from the structure, or data is invalid.
     */
    public function validate(array $structure): void
    {
        $this->type->validate($this->extractValue($structure));
    }

    /**
     * Returns the value for the field, falling back to null (when possible)
     *
     * @param array<string, mixed> $structure
     *
     * @throws SchemaValidationFailure When field is not nullable and missing from the structure.
     */
    private function extractValue(array $structure): mixed
    {
        if (! isset($structure[$this->name]) && ! $this->type->isNullable()) {
            throw SchemaValidationFailure::missingField($this->name);
        }

        return $structure[$this->name] ?? null;
    }
}

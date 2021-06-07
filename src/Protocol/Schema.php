<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol;

use Lcobucci\Kafka\Protocol\Schema\Field;

use function assert;
use function is_array;

/**
 * Represents the fields present in a specific version of a protocol message (request or response)
 */
final class Schema extends Type
{
    /** @var list<Field> */
    private array $fields;

    public function __construct(Field ...$fields)
    {
        $this->fields = $fields;
    }

    public function write(mixed $data, Buffer $buffer): void
    {
        assert(is_array($data));

        foreach ($this->fields as $field) {
            $field->writeTo($data, $buffer);
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @inheritdoc
     */
    public function read(Buffer $buffer): array
    {
        $structure = [];

        foreach ($this->fields as $field) {
            $structure[$field->name()] = $field->readFrom($buffer);
        }

        return $structure;
    }

    public function sizeOf(mixed $data): int
    {
        assert(is_array($data));

        $size = 0;

        foreach ($this->fields as $field) {
            $size += $field->sizeOf($data);
        }

        return $size;
    }

    public function validate(mixed $data): void
    {
        $this->guardAgainstNull($data, 'array');
        $this->guardType($data, 'array', 'is_array');

        foreach ($this->fields as $field) {
            $this->validateField($data, $field);
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws SchemaValidationFailure When value is not valid for given field.
     */
    private function validateField(array $data, Field $field): void
    {
        try {
            $field->validate($data);
        } catch (SchemaValidationFailure $failure) {
            throw SchemaValidationFailure::invalidValueForField($field->name(), $failure);
        }
    }
}

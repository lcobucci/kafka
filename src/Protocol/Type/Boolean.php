<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Type;

/**
 * Represents a boolean variable in a byte.
 *
 * Values 0 and 1 are used to represent false and true respectively.
 * When reading a boolean value, any non-zero value is considered true.
 */
final class Boolean extends Type
{
    public function write(mixed $data, Buffer $buffer): void
    {
        $buffer->writeByte($data === true ? 1 : 0);
    }

    public function read(Buffer $buffer): bool
    {
        return $buffer->readByte() !== 0;
    }

    public function sizeOf(mixed $data): int // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    {
        return 1;
    }

    public function validate(mixed $data): void
    {
        $this->guardAgainstNull($data, 'boolean');
        $this->guardType($data, 'boolean', 'is_bool');
    }
}

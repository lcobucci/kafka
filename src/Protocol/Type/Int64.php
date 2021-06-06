<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Type;

/**
 * Represents an integer between -2^63 and 2^63-1 inclusive.
 */
final class Int64 extends Type
{
    public function write(mixed $data, Buffer $buffer): void
    {
        $buffer->writeLong($data);
    }

    public function read(Buffer $buffer): int
    {
        return $buffer->readLong();
    }

    public function sizeOf(mixed $data): int // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    {
        return 8;
    }

    public function validate(mixed $data): void
    {
        $this->guardAgainstNull($data, 'integer');
        $this->guardType($data, 'integer', 'is_int');
    }
}

<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Type;

/**
 * Represents an integer between -2^15 and 2^15-1 inclusive.
 */
final class Int16 extends Type
{
    private const MIN = -2 ** 15;
    private const MAX = 2 ** 15 - 1;

    public function write(mixed $data, Buffer $buffer): void
    {
        $buffer->writeShort($data);
    }

    public function read(Buffer $buffer): int
    {
        return $buffer->readShort();
    }

    public function sizeOf(mixed $data): int // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    {
        return 2;
    }

    public function validate(mixed $data): void
    {
        $this->guardAgainstNull($data, 'integer');
        $this->guardType($data, 'integer', 'is_int');
        $this->guardRange($data, self::MIN, self::MAX);
    }
}

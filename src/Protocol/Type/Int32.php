<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Type;

/**
 * Represents an integer between -2^31 and 2^31-1 inclusive.
 */
final class Int32 extends Type
{
    private const MIN = -2 ** 31;
    private const MAX = 2 ** 31 - 1;

    public function write(mixed $data, Buffer $buffer): void
    {
        $buffer->writeInt($data);
    }

    public function read(Buffer $buffer): int
    {
        return $buffer->readInt();
    }

    public function sizeOf(mixed $data): int // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    {
        return 4;
    }

    public function validate(mixed $data): void
    {
        $this->guardAgainstNull($data, 'integer');
        $this->guardType($data, 'integer', 'is_int');
        $this->guardRange($data, self::MIN, self::MAX);
    }
}

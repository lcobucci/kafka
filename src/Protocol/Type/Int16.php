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

    /**
     * {@inheritdoc}
     */
    public function write($data, Buffer $buffer): void
    {
        $buffer->writeShort($data);
    }

    /**
     * {@inheritdoc}
     */
    public function read(Buffer $buffer): int
    {
        return $buffer->readShort();
    }

    /**
     * {@inheritdoc}
     */
    public function sizeOf($data): int
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($data): void
    {
        $this->guardAgainstNull($data, 'integer');
        $this->guardType($data, 'integer', 'is_int');
        $this->guardRange($data, self::MIN, self::MAX);
    }
}

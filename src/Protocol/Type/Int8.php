<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Type;

/**
 * Represents an integer between -2^7 and 2^7-1 inclusive.
 */
final class Int8 extends Type
{
    private const MIN = -2 ** 7;
    private const MAX = 2 ** 7 - 1;

    /**
     * {@inheritdoc}
     */
    public function write($data, Buffer $buffer): void
    {
        $buffer->writeByte($data);
    }

    public function read(Buffer $buffer): int
    {
        return $buffer->readByte();
    }

    /**
     * {@inheritdoc}
     */
    public function sizeOf($data): int
    {
        return 1;
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

<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Type;

/**
 * Represents an integer between 0 and 2^32-1 inclusive.
 */
final class UnsignedInt32 extends Type
{
    private const MIN = 0;
    private const MAX = 2 ** 32 - 1;

    /**
     * {@inheritdoc}
     */
    public function write($data, Buffer $buffer): void
    {
        $buffer->writeUnsignedInt($data);
    }

    /**
     * {@inheritdoc}
     */
    public function read(Buffer $buffer): int
    {
        return $buffer->readUnsignedInt();
    }

    /**
     * {@inheritdoc}
     */
    public function sizeOf($data): int
    {
        return 4;
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

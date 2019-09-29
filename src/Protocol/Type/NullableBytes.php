<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Type;
use function assert;

/**
 * Represents a raw sequence of bytes or null.
 *
 * For non-null values, first the length N is given as an integer between -2^31 and 2^31-1 inclusive, then N bytes
 * follow.
 * A null value is encoded with length of -1 and there are no following bytes.
 */
final class NullableBytes extends Type
{
    /**
     * {@inheritdoc}
     */
    public function write($data, Buffer $buffer): void
    {
        if ($data === null) {
            $buffer->writeInt(-1);

            return;
        }

        assert($data instanceof Buffer);

        $length   = $data->remaining();
        $position = $data->position();

        $buffer->writeInt($length);
        $buffer->write($data->get($position, $length));
    }

    /**
     * {@inheritdoc}
     */
    public function read(Buffer $buffer): ?Buffer
    {
        $length = $buffer->readInt();

        if ($length < 0) {
            return null;
        }

        return Buffer::fromContent($buffer->read($length));
    }

    /**
     * {@inheritdoc}
     */
    public function sizeOf($data): int
    {
        if ($data === null) {
            return 4;
        }

        assert($data instanceof Buffer);

        return 4 + $data->remaining();
    }

    /**
     * {@inheritdoc}
     */
    public function isNullable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($data): void
    {
        $this->guardType($data, 'object', 'is_object');
        $this->guardClass($data, Buffer::class);
    }
}

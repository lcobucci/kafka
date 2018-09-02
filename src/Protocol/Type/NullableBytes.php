<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Message;
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
    public function write($data, Message $message): void
    {
        if ($data === null) {
            $message->writeInt(-1);
            return;
        }

        assert($data instanceof Message);

        $length   = $data->remaining();
        $position = $data->position();

        $message->writeInt($length);
        $message->write($data->get($position, $length));
    }

    /**
     * {@inheritdoc}
     */
    public function read(Message $message): ?Message
    {
        $length = $message->readInt();

        if ($length < 0) {
            return null;
        }

        return Message::fromContent($message->read($length));
    }

    /**
     * {@inheritdoc}
     */
    public function sizeOf($data): int
    {
        if ($data === null) {
            return 4;
        }

        assert($data instanceof Message);

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
        $this->guardClass($data, Message::class);
    }
}

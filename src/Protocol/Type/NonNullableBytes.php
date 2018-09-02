<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Message;
use Lcobucci\Kafka\Protocol\Type;
use function assert;

/**
 * Represents a raw sequence of bytes.
 *
 * First the length N is given as an integer between -2^31 and 2^31-1 inclusive, then N bytes follow.
 */
final class NonNullableBytes extends Type
{
    /**
     * {@inheritdoc}
     */
    public function write($data, Message $message): void
    {
        assert($data instanceof Message);

        $length   = $data->remaining();
        $position = $data->position();

        $message->writeInt($length);
        $message->write($data->get($position, $length));
    }

    /**
     * {@inheritdoc}
     */
    public function read(Message $message): Message
    {
        return Message::fromContent(
            $message->read($message->readInt())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function sizeOf($data): int
    {
        assert($data instanceof Message);

        return 4 + $data->remaining();
    }

    /**
     * {@inheritdoc}
     */
    public function validate($data): void
    {
        $this->guardAgainstNull($data, Message::class);
        $this->guardType($data, 'object', 'is_object');
        $this->guardClass($data, Message::class);
    }
}

<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Message;
use Lcobucci\Kafka\Protocol\Type;
use function strlen;

/**
 * Represents a sequence of characters or null.
 *
 * For non-null strings, first the length N is given as a non-negative short, then N bytes follow which are the UTF-8
 * encoding of the character sequence.
 * A null value is encoded with length of -1 and there are no following bytes.
 */
final class NullableString extends Type
{
    private const MAX_LENGTH = 2 ** 15 - 1;

    /**
     * {@inheritdoc}
     */
    public function write($data, Message $message): void
    {
        if ($data === null) {
            $message->writeShort(-1);
            return;
        }

        $message->writeShort(strlen($data));
        $message->write($data);
    }

    /**
     * {@inheritdoc}
     */
    public function read(Message $message): ?string
    {
        $length = $message->readShort();

        if ($length < 0) {
            return null;
        }

        return $message->read($length);
    }

    /**
     * {@inheritdoc}
     */
    public function sizeOf($data): int
    {
        if ($data === null) {
            return 2;
        }

        return 2 + strlen($data);
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
        $this->guardType($data, 'string', 'is_string');
        $this->guardLength($data, self::MAX_LENGTH);
    }
}

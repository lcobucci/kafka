<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Message;
use Lcobucci\Kafka\Protocol\Type;
use function strlen;

/**
 * Represents a sequence of characters.
 *
 * First the length N is given as a non-negative short, then N bytes follow which are the UTF-8 encoding of the
 * character sequence.
 */
final class NonNullableString extends Type
{
    private const MAX_LENGTH = 2 ** 15 - 1;

    /**
     * {@inheritdoc}
     */
    public function write($data, Message $message): void
    {
        $message->writeShort(strlen($data));
        $message->write($data);
    }

    /**
     * {@inheritdoc}
     */
    public function read(Message $message): string
    {
        return $message->read($message->readShort());
    }

    /**
     * {@inheritdoc}
     */
    public function sizeOf($data): int
    {
        return 2 + strlen($data);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($data): void
    {
        $this->guardAgainstNull($data, 'string');
        $this->guardType($data, 'string', 'is_string');
        $this->guardLength($data, self::MAX_LENGTH);
    }
}

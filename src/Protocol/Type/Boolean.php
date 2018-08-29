<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Message;
use Lcobucci\Kafka\Protocol\Type;

/**
 * Represents a boolean variable in a byte.
 *
 * Values 0 and 1 are used to represent false and true respectively.
 * When reading a boolean value, any non-zero value is considered true.
 */
final class Boolean extends Type
{
    /**
     * {@inheritdoc}
     */
    public function write($data, Message $message): void
    {
        $message->writeByte($data === true ? 1 : 0);
    }

    /**
     * {@inheritdoc}
     */
    public function read(Message $message): bool
    {
        return $message->readByte() !== 0;
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
        $this->guardAgainstNull($data, 'boolean');
        $this->guardType($data, 'boolean', 'is_bool');
    }
}

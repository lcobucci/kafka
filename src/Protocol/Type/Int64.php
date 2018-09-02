<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Message;
use Lcobucci\Kafka\Protocol\Type;

/**
 * Represents an integer between -2^63 and 2^63-1 inclusive.
 */
final class Int64 extends Type
{
    /**
     * {@inheritdoc}
     */
    public function write($data, Message $message): void
    {
        $message->writeLong($data);
    }

    /**
     * {@inheritdoc}
     */
    public function read(Message $message): int
    {
        return $message->readLong();
    }

    /**
     * {@inheritdoc}
     */
    public function sizeOf($data): int
    {
        return 8;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($data): void
    {
        $this->guardAgainstNull($data, 'integer');
        $this->guardType($data, 'integer', 'is_int');
    }
}

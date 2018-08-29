<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol;

/**
 * Base interface for any serializable type
 */
abstract class Type
{
    /**
     * Writes given data to the message
     *
     * @param mixed $data
     *
     * @throws NotEnoughBytesAllocated When size of given value is bigger than the remaining allocated bytes.
     */
    abstract public function write($data, Message $message): void;

    /**
     * Returns content from the message
     *
     * @return mixed
     *
     * @throws NotEnoughBytesAllocated When trying to read a content bigger than the remaining allocated bytes.
     */
    abstract public function read(Message $message);

    /**
     * Returns the number of bytes necessary for given value (so that data can be allocated properly)
     *
     * @param mixed $data
     */
    abstract public function sizeOf($data): int;

    /**
     * Validates given value according to type's rules
     *
     * @param mixed $data
     */
    abstract public function validate($data): void;

    /**
     * Returns if the current type allows null values
     */
    public function isNullable(): bool
    {
        return false;
    }
}

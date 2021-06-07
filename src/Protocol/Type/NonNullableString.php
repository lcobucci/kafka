<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
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

    public function write(mixed $data, Buffer $buffer): void
    {
        $buffer->writeShort(strlen($data));
        $buffer->write($data);
    }

    public function read(Buffer $buffer): string
    {
        return $buffer->read($buffer->readShort());
    }

    public function sizeOf(mixed $data): int
    {
        return 2 + strlen($data);
    }

    public function validate(mixed $data): void
    {
        $this->guardAgainstNull($data, 'string');
        $this->guardType($data, 'string', 'is_string');
        $this->guardLength($data, self::MAX_LENGTH);
    }
}

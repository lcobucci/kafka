<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Type;

use function assert;
use function is_a;

/**
 * Represents a raw sequence of bytes.
 *
 * First the length N is given as an integer between -2^31 and 2^31-1 inclusive, then N bytes follow.
 */
final class NonNullableBytes extends Type
{
    /** {@inheritdoc} */
    public function write($data, Buffer $buffer): void
    {
        assert(is_a($data, Buffer::class));

        $length   = $data->remaining();
        $position = $data->position();

        $buffer->writeInt($length);
        $buffer->write($data->get($position, $length));
    }

    public function read(Buffer $buffer): Buffer
    {
        return Buffer::fromContent(
            $buffer->read($buffer->readInt())
        );
    }

    /** {@inheritdoc} */
    public function sizeOf($data): int
    {
        assert(is_a($data, Buffer::class));

        return 4 + $data->remaining();
    }

    /** {@inheritdoc} */
    public function validate($data): void
    {
        $this->guardAgainstNull($data, Buffer::class);
        $this->guardType($data, 'object', 'is_object');
        $this->guardClass($data, Buffer::class);
    }
}

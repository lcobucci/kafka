<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Type;

use function count;

final class ArrayOf extends Type
{
    public function __construct(private Type $type, private bool $nullable = false)
    {
    }

    public function write(mixed $data, Buffer $buffer): void
    {
        if ($data === null) {
            $buffer->writeInt(-1);

            return;
        }

        $buffer->writeInt(count($data));

        foreach ($data as $item) {
            $this->type->write($item, $buffer);
        }
    }

    /**
     * @return list<mixed>|null
     *
     * @inheritdoc
     */
    public function read(Buffer $buffer): ?array
    {
        $count = $buffer->readInt();

        if ($count < 0) {
            return null;
        }

        $items = [];

        for ($i = 0; $i < $count; ++$i) {
            $items[] = $this->type->read($buffer);
        }

        return $items;
    }

    public function sizeOf(mixed $data): int
    {
        if ($data === null) {
            return 4;
        }

        $size = 4;

        foreach ($data as $item) {
            $size += $this->type->sizeOf($item);
        }

        return $size;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function validate(mixed $data): void
    {
        if (! $this->nullable) {
            $this->guardAgainstNull($data, 'array');
        }

        if ($data === null) {
            return;
        }

        $this->guardType($data, 'array', 'is_array');

        foreach ($data as $item) {
            $this->type->validate($item);
        }
    }
}

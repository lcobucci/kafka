<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Type;

use Lcobucci\Kafka\Protocol\Message;
use Lcobucci\Kafka\Protocol\Type;
use function count;

final class ArrayOf extends Type
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var bool
     */
    private $nullable;

    public function __construct(Type $type, bool $nullable = false)
    {
        $this->type     = $type;
        $this->nullable = $nullable;
    }

    /**
     * {@inheritdoc}
     */
    public function write($data, Message $message): void
    {
        if ($data === null) {
            $message->writeInt(-1);

            return;
        }

        $message->writeInt(count($data));

        foreach ($data as $item) {
            $this->type->write($item, $message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read(Message $message): ?array
    {
        $count = $message->readInt();

        if ($count < 0) {
            return null;
        }

        $items = [];

        for ($i = 0; $i < $count; ++$i) {
            $items[] = $this->type->read($message);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function sizeOf($data): int
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

    /**
     * {@inheritdoc}
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($data): void
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

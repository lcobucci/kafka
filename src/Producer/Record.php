<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Producer;

final class Record
{
    /** @param string[]|null $headers */
    public function __construct(private string $topic, private mixed $value, private mixed $key = null, private ?array $headers = null, private ?int $partition = null, private ?int $timestamp = null)
    {
    }

    public function topic(): string
    {
        return $this->topic;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function key(): mixed
    {
        return $this->key;
    }

    /** @return string[]|null */
    public function headers(): ?array
    {
        return $this->headers;
    }

    public function partition(): ?int
    {
        return $this->partition;
    }

    public function timestamp(): ?int
    {
        return $this->timestamp;
    }
}

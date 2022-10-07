<?php
declare(strict_types=1);

namespace Lcobucci\Kafka;

final class Node
{
    public function __construct(public string $id, public string $host, public int $port, public ?string $rack)
    {
    }
}

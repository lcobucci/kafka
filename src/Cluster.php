<?php
declare(strict_types=1);

namespace Lcobucci\Kafka;

use function array_map;
use function explode;

final class Cluster
{
    /** @param Node[] $brokers */
    public function __construct(public ?string $id, public array $brokers)
    {
    }

    public static function bootstrap(string $servers): self
    {
        $id      = 0;
        $brokers = array_map(
            static function (string $server) use (&$id): Node {
                [$host, $port] = explode(':', $server);

                return new Node((string) --$id, $host, (int) $port, null);
            },
            explode(',', $servers),
        );

        return new self(null, $brokers);
    }
}

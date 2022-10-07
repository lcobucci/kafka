<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\API;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Schema\Parser;

abstract class Response
{
    public static function parse(Buffer $buffer, Parser $schemaParser, int $version): static
    {
        $schema = $schemaParser->parse(static::schemaDefinition($version));

        return static::fromArray($schema->read($buffer));
    }

    /** @param array<string, mixed> $data */
    abstract public static function fromArray(array $data): static;

    /** @return array<string, string|array<string, mixed>> */
    abstract public static function schemaDefinition(int $version): array;
}

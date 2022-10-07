<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\API;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Schema\Parser;

abstract class Request
{
    abstract public function apiKey(): int;

    abstract public function highestSupportedVersion(): int;

    public function toBuffer(Parser $schemaParser, int $version): Buffer
    {
        $schema = $schemaParser->parse(static::schemaDefinition($version));
        $data   = $this->asArray($version);

        $buffer = Buffer::allocate($schema->sizeOf($data));
        $schema->write($data, $buffer);

        return $buffer;
    }

    /** @return array<string, string|array<string, mixed>> */
    abstract public static function schemaDefinition(int $version): array;

    /** @return array<string, mixed> */
    abstract public function asArray(int $version): array;

    /** @return class-string<Response> */
    abstract public function responseClass(): string;
}

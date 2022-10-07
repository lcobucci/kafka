<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\API;

use function array_key_last;

final class ApiVersionsRequest extends Request
{
    private const SCHEMA_VERSIONS = [
        self::SCHEMA_V0,
        self::SCHEMA_V1,
        self::SCHEMA_V2,
    ];

    private const SCHEMA_V0 = [];
    private const SCHEMA_V1 = [];
    private const SCHEMA_V2 = [];

    public function apiKey(): int
    {
        return 18;
    }

    public function highestSupportedVersion(): int
    {
        return array_key_last(self::SCHEMA_VERSIONS);
    }

    /** @inheritdoc */
    public static function schemaDefinition(int $version): array
    {
        return self::SCHEMA_VERSIONS[$version];
    }

    /** @inheritdoc */
    public function asArray(int $version): array // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    {
        return [];
    }

    public function responseClass(): string
    {
        return ApiVersionsResponse::class;
    }
}

<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\API;

use Lcobucci\Kafka\Protocol\Type;

final class MetadataResponse extends Response
{
    private const SCHEMA_VERSIONS = [
        self::SCHEMA_V0,
        self::SCHEMA_V1,
        self::SCHEMA_V1,
    ];

    private const SCHEMA_V0 = [
        'error_code' => Type\Int16::class,
        'api_versions' => [
            '_items' => [
                'api_key' => Type\Int16::class,
                'min_version' => Type\Int16::class,
                'max_version' => Type\Int16::class,
            ],
        ],
    ];
    private const SCHEMA_V1 = self::SCHEMA_V0 + ['throttle_time_ms' => Type\Int32::class];

    /** @param list<array{api_key: int, min_version: int, max_version: int}> $apiVersions */
    public function __construct(public int $errorCode, public array $apiVersions, public int $throttleTime)
    {
    }

    /** @inheritdoc */
    public static function fromArray(array $data): static
    {
        return new self(
            $data['error_code'],
            $data['api_versions'],
            $data['throttle_time_ms'] ?? 0,
        );
    }

    /** @inheritdoc */
    public static function schemaDefinition(int $version): array
    {
        return self::SCHEMA_VERSIONS[$version];
    }
}

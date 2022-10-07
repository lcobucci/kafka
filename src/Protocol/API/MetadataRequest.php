<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\API;

use Lcobucci\Kafka\Protocol\Type;

final class MetadataRequest extends Request
{
    private const SCHEMA_VERSIONS = [
        ['topics' => ['_items' => Type\NonNullableString::class]],
        self::SCHEMA_V1,
        self::SCHEMA_V1,
        self::SCHEMA_V1,
        self::SCHEMA_V4,
        self::SCHEMA_V4,
        self::SCHEMA_V4,
        self::SCHEMA_V4,
    ];

    private const SCHEMA_V1 = [
        'topics' => ['_nullable' => true, '_items' => Type\NonNullableString::class],
    ];

    private const SCHEMA_V4 = self::SCHEMA_V1 + ['allow_auto_topic_creation' => Type\Boolean::class];

    /** @param list<string>|null $topics */
    public function __construct(public ?array $topics, public bool $allowTopicCreation)
    {
    }

    public function apiKey(): int
    {
        return 3;
    }

    public function highestSupportedVersion(): int
    {
        return 7;
    }

    /** @inheritdoc */
    public static function schemaDefinition(int $version): array
    {
        return self::SCHEMA_VERSIONS[$version];
    }

    /** @inheritdoc */
    public function asArray(int $version): array
    {
        return [
            'topics' => $version === 0 && $this->topics === null ? [] : $this->topics,
            'allow_auto_topic_creation' => $this->allowTopicCreation,
        ];
    }

    public function responseClass(): string
    {
        return MetadataResponse::class;
    }
}

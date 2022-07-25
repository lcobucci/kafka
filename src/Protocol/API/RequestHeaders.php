<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\API;

use Closure;
use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Message;
use Lcobucci\Kafka\Protocol\Schema\Parser;
use Lcobucci\Kafka\Protocol\Type;

use function assert;

final class RequestHeaders implements Message
{
    private const SCHEMA = [
        'api_key' => Type\Int16::class,
        'api_version' => Type\Int16::class,
        'correlation_id' => Type\Int32::class,
        'client_id' => Type\NullableString::class,
    ];

    public function __construct(
        public int $apiKey,
        public int $apiVersion,
        public int $correlationId,
        public string $client,
        private Closure $responseFactory,
    ) {
    }

    public function toBuffer(Parser $schemaParser): Buffer
    {
        $schema = $schemaParser->parse(self::SCHEMA);

        $data = [
            'api_key' => $this->apiKey,
            'api_version' => $this->apiVersion,
            'correlation_id' => $this->correlationId,
            'client_id' => $this->client,
        ];

        $buffer = Buffer::allocate($schema->sizeOf($data));
        $schema->write($data, $buffer);

        return $buffer;
    }

    public function parseResponse(Buffer $buffer, Parser $schemaParser): Response
    {
        $correlationId = $buffer->readInt();
        assert($this->correlationId === $correlationId);

        return ($this->responseFactory)($buffer, $schemaParser, $this->apiVersion);
    }
}

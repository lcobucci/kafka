<?php
declare(strict_types=1);

use Lcobucci\Kafka\Protocol\Buffer as Message;
use Lcobucci\Kafka\Protocol\Schema;
use Lcobucci\Kafka\Protocol\Type\ArrayOf;
use Lcobucci\Kafka\Protocol\Type\Boolean;
use Lcobucci\Kafka\Protocol\Type\Int16;
use Lcobucci\Kafka\Protocol\Type\Int32;
use Lcobucci\Kafka\Protocol\Type\NonNullableString;
use Lcobucci\Kafka\Protocol\Type\NullableString;
use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

require 'vendor/autoload.php';

$loop = Factory::create();

$int16 = new Int16();
$int32 = new Int32();
$nullableString = new NullableString();

$errorCode = new Schema\Field('error_code', $int16);

$apiVersions = new Schema\Field(
    'api_versions',
    new ArrayOf(
        new Schema(
            new Schema\Field('api_key', $int16),
            new Schema\Field('min_version', $int16),
            new Schema\Field('max_version', $int16)
        )
    )
);

$throttleTime = new Schema\Field('throttle_time_ms', $int32);

$apiVersionsRequest = new Schema();
$apiVersionsResponse = [
    new Schema($errorCode, $apiVersions),
    new Schema($errorCode, $apiVersions, $throttleTime),
    new Schema($errorCode, $apiVersions, $throttleTime),
];

$requestHeader = new Schema(
    new Schema\Field('api_key', $int16),
    new Schema\Field('api_version', $int16),
    new Schema\Field('correlation_id', $int32),
    new Schema\Field('client_id', $nullableString)
);

$responseHeader = new Schema(new Schema\Field('correlation_id', $int32));

const API_VERSIONS = 18;

$schemas = [
    API_VERSIONS => [
        'version' => 2,
        'request' => new Schema(),
        'response' => new Schema($errorCode, $apiVersions, $throttleTime),
    ],
];

$currentId = 0;
$client = 'testing-' . bin2hex(random_bytes(5));
$messages = [];

$createRequest = function (int $correlationId, int $apiKey, array $body = []) use ($schemas, $client, $requestHeader, &$messages): Message {
    $schema = $schemas[$apiKey];
    $bodySchema = $schema['request'];
    assert($bodySchema instanceof Schema);

    $headers = [
        'api_key' => $apiKey,
        'api_version' => $schema['version'],
        'correlation_id' => $correlationId,
        'client_id' => $client,
    ];

    $message = Message::allocate($requestHeader->sizeOf($headers) + $bodySchema->sizeOf($body));
    $requestHeader->write($headers, $message);
    $bodySchema->write($body, $message);

    $messages[$correlationId] = $apiKey;

    return $message;
};

$parseResponse = function (string $rawContent) use ($schemas, $responseHeader, &$messages): array {
    $response = Message::fromContent($rawContent);
    $response->readInt();
    $correlationId = $responseHeader->read($response)['correlation_id'];

    $apiKey = $messages[$correlationId];
    unset($messages[$correlationId]);

    $schema = $schemas[$apiKey]['response'];
    assert($schema instanceof Schema);

    return [
        'correlation_id' => $correlationId,
        'content' => $schema->read($response),
    ];
};

$connector = new Connector($loop);

$connector->connect('localhost:9092')->then(
    static function (ConnectionInterface $connection) use (&$currentId, $createRequest, $parseResponse, $loop): void {
        $retrieveApiKeys = $createRequest(++$currentId, API_VERSIONS);

        $connection->on('data', static function (string $chunk) use ($parseResponse, $connection): void {
            $response = $parseResponse($chunk);
            print_r($response);
            $connection->end();
        });

        $connection->on('error', static function (Throwable $e): void {
            echo $e->getMessage(), PHP_EOL;
        });

        $lengthApiKeys = Message::allocate(4);
        $lengthApiKeys->writeInt($retrieveApiKeys->length());

        $connection->write($lengthApiKeys->bytes());
        $connection->write($retrieveApiKeys->bytes());
    }
)->otherwise(
    static function (Throwable $e): void {
        echo $e->getMessage(), PHP_EOL;
    }
);

$loop->run();

//######################################################
//
//$producer = new Producer();
//$producer->send();
//$producer->close();

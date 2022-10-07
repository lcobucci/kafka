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

$schemaParser = new Schema\Parser();

$int16 = new Int16();
$int32 = new Int32();

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
$messages = [1 => API_VERSIONS];

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

$connector->connect('localhost:9093')->then(
    static function (ConnectionInterface $connection) use ($parseResponse, $loop): void {
        $loop->addSignal(
            SIGINT,
            $func = static function () use ($connection, $loop, &$func): void {
                $connection->end();
                $loop->removeSignal(SIGINT, $func);
            }
        );

        $connection->on('data', static function (string $chunk) use ($parseResponse, $func): void {
            $response = $parseResponse($chunk);
            print_r($response);
            $func();
        });

        $connection->on('error', static function (Throwable $e): void {
            echo $e->getMessage(), PHP_EOL;
        });


        $connection->write(hex2bin('0000001c'));
        $connection->write(hex2bin('0012000200000001001274657374696e672d64656339303232333833'));
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

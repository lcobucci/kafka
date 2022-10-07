<?php
declare(strict_types=1);

use Lcobucci\Kafka\Client\Channel;
use Lcobucci\Kafka\Cluster;
use Lcobucci\Kafka\Protocol\API\ApiVersionsRequest;
use Lcobucci\Kafka\Protocol\API\Response;
use Lcobucci\Kafka\Protocol\Schema\Parser;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use React\EventLoop\Loop;
use React\Socket\Connector;

require 'vendor/autoload.php';

$logger = new Logger('test-channel', [new StreamHandler(STDERR)]);

$cluster = Cluster::bootstrap('localhost:9093,localhost:9094');
$channel = new Channel(
    new Connector(),
    $logger,
    new Parser(),
    $cluster->brokers[random_int(0, 1)]
);

Loop::addSignal(
    SIGINT,
    static function () use ($channel): void {
        $channel->disconnect();

        Loop::stop();
    }
);

$channel->send(new ApiVersionsRequest(), 0, 'producer-test')->then(
    static function (Response $response) use ($logger): void {
        $logger->debug('Response received', ['response' => $response]);
    }
);

Loop::run();

<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Client;

use Lcobucci\Kafka\Node;
use Lcobucci\Kafka\Protocol\API\Request;
use Lcobucci\Kafka\Protocol\API\RequestHeaders;
use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Schema\Parser;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;
use SplPriorityQueue;
use SplQueue;
use Throwable;

use function assert;
use function min;

final class Channel
{
    /** @var SplPriorityQueue<int, array{array{Request, int, string}, Deferred}> */
    private SplPriorityQueue $processingQueue;
    /** @var SplQueue<array{RequestHeaders, Deferred}> */
    private SplQueue $inFlightQueue;
    private ?ConnectionInterface $connection = null;
    private readonly LoopInterface $loop;

    public function __construct(
        private ConnectorInterface $connector,
        private LoggerInterface $logger,
        private Parser $schemaParser,
        private Node $node,
    ) {
        $this->loop            = Loop::get();
        $this->processingQueue = new SplPriorityQueue();
        $this->inFlightQueue   = new SplQueue();
    }

    public function send(Request $request, int $correlation, string $client): PromiseInterface
    {
        $this->ensureConnected();

        $deferred = new Deferred();
        $this->processingQueue->insert([[$request, $correlation, $client], $deferred], 0);

        return $deferred->promise();
    }

    private function ensureConnected(): void
    {
        if ($this->connection !== null) {
            return;
        }

        $this->connect();
    }

    private function connect(): void
    {
        $this->logger->info('Opening connection to node', ['node' => $this->node]);

        $this->connector->connect($this->node->host . ':' . $this->node->port)->then(
            $this->initializeConnection(...),
            function (Throwable $throwable): void {
                $this->logger->error(
                    'Error while connecting to node #' . $this->node->id,
                    ['node' => $this->node, 'exception' => $throwable],
                );

                $this->loop->addTimer(1, $this->connect(...));
            },
        );
    }

    public function initializeConnection(ConnectionInterface $connection): void
    {
        $this->logger->info('Connection to node established', ['node' => $this->node]);

        $this->connection = $connection;
        $this->connection->on('data', $this->onData(...));
        $this->connection->on('error', $this->cleanUpConnection(...));
        $this->connection->on('close', $this->cleanUpConnection(...));

        $this->loop->futureTick($this->processQueue(...));
    }

    public function processQueue(): void
    {
        if (! $this->processingQueue->valid()) {
            return;
        }

        $this->logger->debug('Processing message queue of node', ['node' => $this->node]);

        for ($i = 0, $max = min(15, $this->processingQueue->count()); $i < $max; ++$i) {
            [[$request, $correlation, $client], $deferred] = $this->processingQueue->current();

            $this->processingQueue->next();
            $headers = $this->sendMessage($request, $correlation, $client);
            $this->inFlightQueue->enqueue([$headers, $deferred]);
        }

        $this->loop->futureTick($this->processQueue(...));
    }

    private function sendMessage(Request $request, int $correlation, string $client): RequestHeaders
    {
        $headers = new RequestHeaders(
            $request->apiKey(),
            $request->highestSupportedVersion(),
            $correlation,
            $client,
            $request->responseClass()::parse(...),
        );

        $header = $headers->toBuffer($this->schemaParser);
        $body   = $request->toBuffer($this->schemaParser, $headers->apiVersion);

        $length = Buffer::allocate(4);
        $length->writeInt($header->length() + $body->length());

        assert($this->connection instanceof ConnectionInterface);
        $this->connection->write($length->bytes() . $header->bytes() . $body->bytes());

        return $headers;
    }

    public function onData(string $data): void
    {
        $this->logger->debug('Message received', ['node' => $this->node]);

        [$headers, $deferred] = $this->inFlightQueue->dequeue();

        $buffer = Buffer::fromContent($data);
        $length = $buffer->readInt();

        $deferred->resolve($headers->parseResponse(Buffer::fromContent($buffer->read($length)), $this->schemaParser));
    }

    public function disconnect(): void
    {
        if ($this->connection === null) {
            return;
        }

        $this->connection->end();
    }

    public function cleanUpConnection(): void
    {
        $this->logger->info('Closing connection to node', ['node' => $this->node]);

        $this->connection = null;
    }
}

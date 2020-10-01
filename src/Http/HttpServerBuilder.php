<?php

namespace App\Http;

use Amp\Http\Server\Driver\DefaultClientFactory;
use Amp\Http\Server\Driver\DefaultHttpDriverFactory;
use Amp\Http\Server\Driver\Http2Driver;
use Amp\Http\Server\Driver\TimeoutCache;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Options;
use Amp\Http\Server\ServerObserver;
use App\Domain\Sockets;
use App\Handler\ContextAwareErrorHandlerInterface;
use App\Handler\ContextAwareRequestHandlerInterface;
use App\Socket\SocketFactory;
use App\Logger\ConsoleLoggerFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class HttpServerBuilder implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private array $sockets = [];
    private SocketFactory $socketFactory;
    private ContextAwareRequestHandlerInterface $requestHandler;
    private ContextAwareErrorHandlerInterface $errorHandler;
    private ServerObserver $serverObserver;

    public function __construct(SocketFactory $socketFactory, ContextAwareRequestHandlerInterface $requestHandler, ContextAwareErrorHandlerInterface $errorHandler, ServerObserver $serverObserver)
    {
        $this->socketFactory = $socketFactory;
        $this->requestHandler = $requestHandler;
        $this->errorHandler = $errorHandler;
        $this->serverObserver = $serverObserver;
    }

    public function addSocket(bool $ipv6 = false, bool $tls = false, ?int $port = null): HttpServerBuilder
    {
        $defaultPort = $tls ? Sockets::DEFAULT_TLS_PORT : Sockets::DEFAULT_PORT;
        $this->sockets[] = $this->socketFactory
            ->setTls($tls)
            ->setIp(!$ipv6 ? Sockets::DEFAULT_IPV4_HOST : Sockets::DEFAULT_IPV6_HOST)
            ->setPort(empty($port) ? $defaultPort : $port)
            ->build();
        return $this;
    }

    public function build(): HttpServer
    {
        $option = (new Options())->withRequestLogContext()->withHttp2Upgrade()->withPush()->withDebugMode();
        $http2Driver = new Http2Driver($option, $this->logger);

        $server = new HttpServer($this->sockets, $this->requestHandler, ConsoleLoggerFactory::create());
        $httpDriverFactory = new DefaultHttpDriverFactory();

        /** @var \Amp\Socket\Server $socket */
        foreach ($this->sockets as $socket) {
            $remoteClient = (new DefaultClientFactory())->createClient($socket->getResource(), $this->requestHandler, $this->errorHandler, $this->logger, $option, new TimeoutCache());
            $httpDriverFactory->selectDriver($remoteClient, $this->errorHandler, $this->logger, $option);
        }
        /** @var \Amp\Socket\Server $socket */

        $server->setDriverFactory($httpDriverFactory);
        $server->attach($this->serverObserver);

        return $server;
    }
}

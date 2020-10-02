<?php

namespace App\Server;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\ServerObserver as ServerObserverInterface;
use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class ServerObserver implements ServerObserverInterface, LoggerAwareInterface, SerializerAwareInterface
{
    use LoggerAwareTrait;
    use SerializerAwareTrait;

    public function onStart(HttpServer $server): Promise
    {
        $this->logger->notice("● Server is active");
        $this->logger->debug($this->serializer->serialize($server->getOptions(), JsonEncoder::FORMAT));
        return new Success();
    }

    public function onStop(HttpServer $server): Promise
    {
        $this->logger->warning("○ Server is stopped");
        return new Success();
    }

}

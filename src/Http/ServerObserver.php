<?php

namespace App\Http;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\ServerObserver as ServerObserverInterface;
use Amp\Promise;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class ServerObserver implements ServerObserverInterface, LoggerAwareInterface, SerializerAwareInterface
{
    use LoggerAwareTrait;
    use SerializerAwareTrait;

    public function onStart(HttpServer $server): Promise
    {
        $this->logger->info("● Server is active");
        $this->logger->debug($this->serializer->serialize($server->getOptions(), JsonEncoder::FORMAT));
    }

    public function onStop(HttpServer $server): Promise
    {
        $this->logger->notice("○ Server is stopped");
    }

}

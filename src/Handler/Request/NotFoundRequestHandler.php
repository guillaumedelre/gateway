<?php

namespace App\Handler\Request;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Promise;
use Amp\Success;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

final class NotFoundRequestHandler implements RequestHandlerInterface, LoggerAwareInterface, SerializerAwareInterface
{
    use LoggerAwareTrait;
    use SerializerAwareTrait;

    public function handleRequest(Request $request, array $context = []): Promise
    {
        $message = sprintf(
            "%s - %s request %s using http%d: %s.",
            Status::NOT_FOUND,
            $request->getMethod(),
            $request->getUri()->__toString(),
            $request->getProtocolVersion(),
            Status::getReason(Status::NOT_FOUND)
        );
        $this->logger->warning($message);

        return new Success(new Response(Status::NOT_FOUND, ["content-type" => "text/plain; charset=utf-8"], ''));
    }

    public function supportsRequest(Request $request, array &$context = []): bool
    {
        return true;
    }

}

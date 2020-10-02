<?php

namespace App\Handler\Error;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Promise;
use Amp\Success;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

final class DefaultErrorHandler implements ErrorHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** {@inheritdoc} */
    public function handleError(int $statusCode, string $reason = null, Request $request = null, array $context = []): Promise
    {
        $this->logger->warning("$statusCode - {$request->getMethod()} request {$request->getUri()->__toString()} using http{$request->getProtocolVersion()}: $reason");

        return new Success(new Response($statusCode, ["content-type" => "text/plain; charset=utf-8"], ''));
    }

    public function supportsRequest(Request $request, array &$context = []): bool
    {
        return true;
    }

}

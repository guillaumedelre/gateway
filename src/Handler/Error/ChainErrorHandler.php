<?php

namespace App\Handler;

use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\Request;
use Amp\Promise;

final class ChainErrorHandler implements ContextAwareErrorHandlerInterface
{
    protected iterable $handlers;
    protected array $handlerByRequestHash = [];

    public function __construct(iterable $handlers = [])
    {
        $this->handlers = $handlers;
    }

    public function handleError(int $statusCode, string $reason = null, Request $request = null, array $context = []): Promise
    {
        return $this->getHandler($request, $context)->handleError($statusCode, $reason, $request, $context);
    }

    public function supportsRequest(Request $request, array &$context = []): bool
    {
        try {
            $this->getHandler($request, $context);
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    private function getHandler(Request $request, array $context): ErrorHandler
    {
        $requestHash = \spl_object_hash($request);
        if (isset($this->handlerByRequestHash[$requestHash])
            && isset($this->handlers[$this->handlerByRequestHash[$requestHash]])
        ) {
            return $this->handlers[$this->handlerByRequestHash[$requestHash]];
        }

        foreach ($this->handlers as $i => $handler) {
            if ($handler->supportsRequest($request, $context)) {
                $this->handlerByRequestHash[$requestHash] = $i;

                return $handler;
            }
        }

        throw new \RuntimeException(sprintf('No handler found for request "%s".', $requestHash));
    }
}

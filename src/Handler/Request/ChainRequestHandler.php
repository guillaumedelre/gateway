<?php

namespace App\Handler\Request;

use Amp\Failure;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Promise;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

final class ChainRequestHandler implements RequestHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected iterable $handlers;
    protected array $handlerByRequestHash = [];

    public function __construct(iterable $handlers = [])
    {
        $this->handlers = $handlers;
    }

    public function handleRequest(Request $request, array $context = []): Promise
    {
        try {
            return $this->getHandler($request, $context)->handleRequest($request, $context);
        } catch (\Throwable $exception) {
            return new Failure($exception);
        }
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

    /**
     * @param Request $request
     * @param array   $context
     *
     * @return RequestHandler|RequestHandlerInterface
     */
    private function getHandler(Request $request, array &$context)
    {
        $requestHash = \spl_object_hash($request);
        if (isset($this->handlerByRequestHash[$requestHash])
            && isset($this->handlers[$this->handlerByRequestHash[$requestHash]])
        ) {
            return $this->handlers[$this->handlerByRequestHash[$requestHash]];
        }

        foreach ($this->handlers as $handler) {
            if ($handler === $this) {
                continue;
            }
            if ($handler->supportsRequest($request, $context)) {
                $this->handlerByRequestHash[$requestHash] = $handler;

                return $handler;
            }
        }

        throw new \RuntimeException(sprintf('No handler found for request "%s".', $requestHash));
    }
}

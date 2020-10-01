<?php

namespace App\Handler;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Promise;

interface ContextAwareMiddlewareHandlerInterface extends Middleware
{
    /**
     * @param Request        $request
     * @param RequestHandler $requestHandler
     * @param array          $context Additional contextual data.
     *
     * @return Promise<\Amp\Http\Server\Response>
     */
    public function handleRequest(Request $request, RequestHandler $requestHandler, array $context = []): Promise;

    /**
     * @param Request $request
     * @param array $context options that handlers have access to
     *
     * @return bool
     */
    public function supportsRequest(Request $request, array &$context = []): bool;
}

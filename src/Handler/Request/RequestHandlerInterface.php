<?php

namespace App\Handler\Request;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Promise;

interface RequestHandlerInterface extends RequestHandler
{
    /**
     * @param Request $request
     * @param array $context Additional contextual data.
     *
     * @return Promise<\Amp\Http\Server\Response>
     */
    public function handleRequest(Request $request, array $context = []): Promise;

    /**
     * @param Request $request
     * @param array $context options that handlers have access to
     *
     * @return bool
     */
    public function supportsRequest(Request $request, array &$context = []): bool;
}

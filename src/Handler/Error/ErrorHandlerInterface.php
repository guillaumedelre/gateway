<?php

namespace App\Handler\Error;

use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\Request;
use Amp\Promise;

interface ErrorHandlerInterface extends ErrorHandler
{
    /**
     * @param int          $statusCode Error status code, 4xx or 5xx.
     * @param string|null  $reason Reason message. Will use the status code's default reason if not provided.
     * @param Request|null $request Null if the error occurred before parsing the request completed.
     * @param array        $context Additional contextual data.
     *
     * @return Promise
     */
    public function handleError(int $statusCode, string $reason = null, Request $request = null, array $context = []): Promise;

    /**
     * @param Request $request
     * @param array $context options that handlers have access to
     *
     * @return bool
     */
    public function supportsRequest(Request $request, array &$context = []): bool;
}

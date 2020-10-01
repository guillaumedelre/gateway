<?php

namespace App\Handler;

use Amp\Http\Server\Response;
use Amp\Success;
use Amp\Http\Server\Request;
use Amp\Promise;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

final class DefaultErrorHandler implements ContextAwareErrorHandlerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /** @var string[] */
    private $cache = [];

    /** {@inheritdoc} */
    public function handleError(int $statusCode, string $reason = null, Request $request = null, array $context = []): Promise
    {
        $errorJson = [
            'statusCode' => $statusCode,
            'reason' => $reason,
            'request' => $this->serializer->serialize($request, JsonEncoder::FORMAT),
            'context' => $context,
        ];

        if (!isset($this->cache[$statusCode])) {
            $this->cache[$statusCode] = $errorJson;
        }

        $response = new Response($statusCode, [
            "content-type" => "application/json; charset=utf-8"
        ], $this->cache[$statusCode]);

        $response->setStatus($statusCode, $reason);

        return new Success($response);
    }

    public function supportsRequest(Request $request, array &$context = []): bool
    {
        return true;
    }

}

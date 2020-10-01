<?php

namespace App\Handler;

use Amp\Http\Server\Request;
use Amp\Promise;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

final class DefaultRequestHandler implements ContextAwareRequestHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private RouterInterface $router;

    public function __construct(RouterInterface  $router)
    {
        $this->router = $router;
    }

    public function handleRequest(Request $request, array $context = []): Promise
    {
        $this->logger->debug($request->getUri()->__toString());

        return (new $context['_controller']())($request);
    }

    public function supportsRequest(Request $request, array &$context = []): bool
    {
        try {
            $requestContext = new RequestContext(
                '/',
                $request->getMethod(),
                $request->getUri()->getHost(),
            );

            $this->router->setContext($requestContext);
            $parameters = $this->router->match($request->getUri()->getPath());
        } catch (NoConfigurationException $exception) {
            return false;
        } catch (ResourceNotFoundException $exception) {
            return false;
        } catch (MethodNotAllowedException $exception) {
            return false;
        }

        $context += !empty($parameters) ? $parameters : [];
    }

}

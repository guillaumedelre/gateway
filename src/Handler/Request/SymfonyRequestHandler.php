<?php

namespace App\Handler\Request;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Promise;
use Amp\Success;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

final class SymfonyRequestHandler implements RequestHandlerInterface, LoggerAwareInterface, ContainerAwareInterface
{
    use LoggerAwareTrait;
    use ContainerAwareTrait;

    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function handleRequest(Request $request, array $context = []): Promise
    {
        $controller = $this->container->get($context['_controller']);

        /** @var Response $response */
        $response = $controller->__invoke($request);

        $message = sprintf(
            "%s - %s request %s using http%d.",
            $response->getStatus(),
            $request->getMethod(),
            $request->getUri()->__toString(),
            $request->getProtocolVersion()
        );
        if ($response->getStatus() < Status::BAD_REQUEST) {
            $this->logger->notice($message);
        } else {
            $this->logger->warning(sprintf("%s: %s.", $message, $response->getReason()));
        }

        $this->logger->debug(sprintf("Memory usage: %s", Helper::formatMemory(memory_get_usage(true))));

        return new Success($response);
    }

    public function supportsRequest(Request $request, array &$context = []): bool
    {
        $requestContext = new RequestContext('/', $request->getMethod(), $request->getUri()->getHost());
        $this->router->setContext($requestContext);
        try {
            $parameters = $this->router->match($request->getUri()->getPath());
            $context += $parameters;

            return $this->container->has($parameters['_controller']);
        } catch (NoConfigurationException | ResourceNotFoundException | MethodNotAllowedException $exception) {
            return false;
        }
    }

}

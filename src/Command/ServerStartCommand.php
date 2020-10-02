<?php

namespace App\Command;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Options;
use Amp\Loop;
use Amp\Socket\BindContext;
use Amp\Socket\Certificate;
use Amp\Socket\Server;
use Amp\Socket\ServerTlsContext;
use App\Handler\Error\ErrorHandlerInterface;
use App\Handler\Request\RequestHandlerInterface;
use App\Server\ServerObserver;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ServerStartCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected static $defaultName = 'server:start';

    private string $projectDir;
    private RequestHandlerInterface $requestHandler;
    private ErrorHandlerInterface $errorHandler;
    private ServerObserver $serverObserver;

    public function __construct(string $projectDir, RequestHandlerInterface $requestHandler, ErrorHandlerInterface $errorHandler, ServerObserver $serverObserver)
    {
        parent::__construct(self::$defaultName);

        $this->projectDir = $projectDir;
        $this->requestHandler = $requestHandler;
        $this->errorHandler = $errorHandler;
        $this->serverObserver = $serverObserver;
    }

    protected function configure()
    {
        $this->setDescription('AMP Http Server')
            ->addOption("port", null, InputOption::VALUE_OPTIONAL, "The port used to listen for non-tls socket", 80)
            ->addOption("ssl", null, InputOption::VALUE_OPTIONAL, "The ssl port used to listen for tls socket", 443)
            ->addOption("no-tls", null, InputOption::VALUE_OPTIONAL, "If true, ssl is disabled", false)
            ->addOption("timeout", null, InputOption::VALUE_OPTIONAL, "Server timeout in second", 5)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        Loop::run(
            function () use ($input) {
                $timeout = (int)$input->getOption('timeout');
                $noTls = filter_var($input->getOption('no-tls'), FILTER_VALIDATE_BOOLEAN);
                $cert = new Certificate($this->projectDir . '/config/certs/localhost.pem');

                $context = $noTls ? null : (new BindContext())->withTlsContext((new ServerTlsContext())->withDefaultCertificate($cert));

                $options = (new Options())
                    ->withRequestLogContext()
                    ->withHttp1Timeout($timeout);

                if (!$noTls) {
                    $options
                        ->withHttp2Timeout($timeout)
                        ->withTlsSetupTimeout($timeout);
                }

                $sockets = [
                    Server::listen("0.0.0.0:{$input->getOption('port')}"),
                    Server::listen("[::]:{$input->getOption('port')}"),
                ];

                if (!$noTls) {
                    $sockets = array_merge($sockets, [
                        Server::listen("0.0.0.0:{$input->getOption('ssl')}", $context),
                        Server::listen("[::]:{$input->getOption('ssl')}", $context),
                    ]);
                }

                $server = new HttpServer($sockets, $this->requestHandler, $this->logger, $options);
                $server->setErrorHandler($this->errorHandler);
                $server->attach($this->serverObserver);

                yield $server->start();

                Loop::onSignal(SIGINT, function (string $watcherId) use ($server) {
                    Loop::cancel($watcherId);
                    yield $server->stop();
                });
            }
        );

        return Command::SUCCESS;
    }
}

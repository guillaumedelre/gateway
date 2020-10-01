<?php

namespace App\Command;

use Amp\Loop;
use App\Domain\Sockets;
use App\Http\HttpServerBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ServerStartCommand extends Command
{
    protected static $defaultName = 'server:start';

    private HttpServerBuilder $httpServerBuilder;

    public function __construct(HttpServerBuilder $httpServerBuilder)
    {
        parent::__construct(self::$defaultName);

        $this->httpServerBuilder = $httpServerBuilder;
    }

    protected function configure()
    {
        $this
            ->setDescription('AMP Http Server')
            ->addOption("port", "p", InputOption::VALUE_REQUIRED, "The port to be listen", Sockets::DEFAULT_PORT)
            ->addOption("tls", "t", InputOption::VALUE_OPTIONAL, "The tsl port to be listen")
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $port = (int)$input->getOption("port");
        $this->httpServerBuilder
            ->addSocket(false, false, $port)
            ->addSocket(true, false, $port);

        $tlsPort = (int)$input->getOption("tls");
        if (!empty($tlsPort)) {
            $this->httpServerBuilder
                ->addSocket(false, true, $tlsPort)
                ->addSocket(true, true, $tlsPort);
        }

        $httpServer = $this->httpServerBuilder->build();

        Loop::run(function () use ($httpServer) {
            yield $httpServer->start();

            Loop::onSignal(SIGINT, function (string $watcherId) use ($httpServer) {
                Loop::cancel($watcherId);
                yield $httpServer->stop();
            });
        });

        return Command::SUCCESS;
    }
}

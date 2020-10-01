<?php

namespace App\Socket;

use Amp\Socket\BindContext;
use Amp\Socket\Certificate;
use Amp\Socket\Server;
use Amp\Socket\ServerTlsContext;
use App\Builder\SocketConfigurationBuilder;

class SocketFactory
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function createFrom(SocketConfigurationBuilder $socketConfigurationBuilder): Server
    {
        $tlsContext = null;

        /** @var SocketConfiguration $socketConfiguration */
        $socketConfiguration = $socketConfigurationBuilder->build();
        if ($socketConfiguration->isTls()) {
            $cert = new Certificate("{$this->projectDir}/config/certs/cert.pem");
            $tlsContext = (new BindContext())->withTlsContext((new ServerTlsContext())->withDefaultCertificate($cert));
        }

        return Server::listen($socketConfiguration->getIp() . ':' . $socketConfiguration->getPort(), $tlsContext);
    }
}

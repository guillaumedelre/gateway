<?php

namespace App\Socket;

use App\Domain\Sockets;

class SocketConfiguration
{
    private bool $tls = false;
    private string $ip = Sockets::DEFAULT_IPV4_HOST;
    private int $port = Sockets::DEFAULT_PORT;

    public function isTls(): bool
    {
        return $this->tls;
    }

    public function setTls(bool $tls): SocketConfiguration
    {
        $this->tls = $tls;
        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): SocketConfiguration
    {
        $this->ip = $ip;
        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): SocketConfiguration
    {
        $this->port = $port;
        return $this;
    }
}

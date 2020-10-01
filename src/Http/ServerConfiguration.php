<?php

namespace App\Http;

class ServerConfiguration
{
    private string $name;
    private iterable $sockets;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ServerConfiguration
    {
        $this->name = $name;
        return $this;
    }

    public function getSockets(): iterable
    {
        return $this->sockets;
    }

    public function setSockets(iterable $sockets): ServerConfiguration
    {
        $this->sockets = $sockets;
        return $this;
    }

}

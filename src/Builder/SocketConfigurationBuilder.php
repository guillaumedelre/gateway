<?php

namespace App\Builder;


use App\Model\SocketConfiguration;

class SocketConfigurationBuilder implements ObjectBuilderInterface
{
    use ObjectBuilderTrait;

    public function reset(): self
    {
        $this->object = new SocketConfiguration();

        return $this;
    }
}

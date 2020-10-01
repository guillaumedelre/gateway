<?php

namespace App\Builder;

interface ObjectBuilderInterface
{
    public function reset(): self;
    public function build(): object;
    public function setObject(object $object): void;
}

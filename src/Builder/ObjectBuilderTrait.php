<?php

namespace App\Builder;

use Doctrine\Inflector\Inflector;

trait ObjectBuilderTrait
{
    private $object;

    abstract public function reset(): self;

    public function build(): object
    {
        $return = $this->getObject();
        $this->reset();

        return $return;
    }

    private function getObject(): object
    {
        if (!$this->object) {
            $this->reset();
        }

        return $this->object;
    }

    public function setObject(object $object): void
    {
        $this->reset();
        $expectedClass = get_class($this->object);

        if (!is_object($object) || $expectedClass !== ($givenClass = get_class($object))) {
            if (!isset($givenClass)) {
                $givenClass = gettype($object);
            }

            throw new \InvalidArgumentException(
                sprintf('Expected object of type "%s", given "%s"', $expectedClass, $givenClass)
            );
        }

        unset($this->object);
        $this->object = $object;
    }

    public function __call(string $name, array $args): self
    {
        if (0 !== strpos($name, 'with')) {
            $this->badMethodException($name);
        }

        $object = $this->getObject();
        $setter = Inflector::camelize('set' . mb_substr($name, 4));

        if (!method_exists($object, $setter)) {
            $this->badMethodException($name);
        }

        call_user_func_array([$object, $setter], $args);

        return $this;
    }

    private function badMethodException(string $name)
    {
        throw new \BadMethodCallException(sprintf('Unknown method "%s"', $name));
    }
}

<?php
namespace Rczy\Phipe\Operation;

trait Terminal
{
    public function reduce(mixed $initialValue, callable $reducer): mixed
    {
        $accumulator = $initialValue;
        foreach ($this->source as $item) {
            $accumulator = $reducer($accumulator, $item);
        }
        return $accumulator;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->source as $item) {
            $result[] = $item;
        }
        return $result;
    }

    public function forEach(callable $consumer): void
    {
        foreach ($this->source as $item) {
            $consumer($item);
        }
    }
}

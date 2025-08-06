<?php
namespace Rczy\Phipe\Operation;

trait Terminal
{
    public function reduce(mixed $initialValue, callable $reducer): mixed
    {
        $accumulator = $initialValue;
        foreach ($this->source as $key => $item) {
            $accumulator = $reducer($accumulator, $item, $key);
        }
        return $accumulator;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->source as $key => $item) {
            $result[$key] = $item;
        }
        return $result;
    }

    public function forEach(callable $consumer): void
    {
        foreach ($this->source as $key => $item) {
            $consumer($item, $key);
        }
    }
}

<?php
namespace Rczy\Phipe\Operation;

trait Terminal
{
    /**
     * Applies the reducer function iteratively on all the items of
     * the source while consuming it, to produce a single value.
     * Terminal operation.
     * 
     * reducer: fn ($accumulator, $item, $key)
     * 
     * @param mixed $initialValue
     * @param callable $reducer
     * @return mixed
     */
    public function reduce(mixed $initialValue, callable $reducer): mixed
    {
        $accumulator = $initialValue;
        foreach ($this->source as $key => $item) {
            $accumulator = $reducer($accumulator, $item, $key);
        }
        return $accumulator;
    }

    /**
     * Consumes the pipeline and build an array of it's items.
     * Terminal operation.
     * 
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->source as $key => $item) {
            $result[$key] = $item;
        }
        return $result;
    }

    /**
     * Consumes the pipeline and applies a consumer function on all items.
     * Terminal operation.
     * 
     * consumer: fn ($item, $key)
     * 
     * @param callable $consumer
     * @return void
     */
    public function forEach(callable $consumer): void
    {
        foreach ($this->source as $key => $item) {
            $consumer($item, $key);
        }
    }

    /**
     * Consumes the pipeline and returns the first item from it.
     * Terminal operation.
     * 
     * @return mixed
     */
    public function head(): mixed
    {
        foreach ($this->source as $item) {
            return $item;
        }
        return null;
    }

    /**
     * Consumes the pipeline and returns the last item from it.
     * Terminal operation.
     * 
     * @return mixed
     */
    public function tail(): mixed
    {
        $last = null;
        foreach ($this->source as $item) {
            $last = $item;
        }
        return $last;
    }
}

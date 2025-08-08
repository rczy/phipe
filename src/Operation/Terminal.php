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
     * Short-circuiting terminal operation.
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

    /**
     * Consumes the pipeline and returns the number of items.
     * Terminal operation.
     * 
     * @return int
     */
    public function count(): int
    {
        $count = 0;
        foreach ($this->source as $_) {
            $count++;
        }
        return $count;
    }

    /**
     * Consumes the pipeline and returns the minimum item.
     * If a value mapper is provided, the minimum is determined by the result of that function.
     * Terminal operation.
     * 
     * valueMapper: fn ($item)
     * 
     * @param null|callable $valueMapper
     * @return mixed
     */
    public function min(?callable $valueMapper = null): mixed
    {
        $min = null;
        $firstIteration = true;
        foreach ($this->source as $item) {
            $current = $valueMapper ? $valueMapper($item) : $item;
            if ($firstIteration) {
                $min = $current;
                $firstIteration = false;
                continue;
            }
            $min = $current < $min ? $current : $min;
        }
        return $min;
    }

    /**
     * Consumes the pipeline and returns the maximum item.
     * If a value mapper is provided, the maximum is determined by the result of that function.
     * Terminal operation.
     * 
     * valueMapper: fn ($item)
     * 
     * @param null|callable $valueMapper
     * @return mixed
     */
    public function max(?callable $valueMapper = null): mixed
    {
        $max = null;
        $firstIteration = true;
        foreach ($this->source as $item) {
            $current = $valueMapper ? $valueMapper($item) : $item;
            if ($firstIteration) {
                $max = $current;
                $firstIteration = false;
                continue;
            }
            $max = $current > $max ? $current : $max;
        }
        return $max;
    }

    /**
     * Consumes the pipeline and returns the sum of items.
     * If a value mapper is provided, the sum is determined by the result of that function.
     * Terminal operation.
     * 
     * valueMapper: fn ($item)
     * 
     * @param null|callable $valueMapper
     * @return mixed
     */
    public function sum(?callable $valueMapper = null): mixed
    {
        $sum = null;
        $firstIteration = true;
        foreach ($this->source as $item) {
            $current = $valueMapper ? $valueMapper($item) : $item;
            if ($firstIteration) {
                $sum = $current;
                $firstIteration = false;
                continue;
            }
            $sum += $current;
        }
        return $sum;
    }

    /**
     * Consumes the pipeline and returns the average of items.
     * If a value mapper is provided, the average is determined by the result of that function.
     * Terminal operation.
     * 
     * valueMapper: fn ($item)
     * 
     * @param null|callable $valueMapper
     * @return mixed
     */
    public function avg(?callable $valueMapper = null): mixed
    {
        $sum = null;
        $count = 0;
        $firstIteration = true;
        foreach ($this->source as $item) {
            $count++;
            $current = $valueMapper ? $valueMapper($item) : $item;
            if ($firstIteration) {
                $sum = $current;
                $firstIteration = false;
                continue;
            }
            $sum += $current;
        }
        return $count ? $sum / $count : null;
    }

    /**
     * Consumes the pipeline and returns the concatenated string representation of
     * the items, with an optional separator between them.
     * Terminal operation.
     * 
     * @param null|string $separator
     * @return string
     */
    public function join(?string $separator = ""): string
    {
        $joined = "";
        foreach ($this->source as $item) {
            $joined .= $item . $separator;
        }
        return substr($joined, 0, -strlen($separator));
    }

    /**
     * Consumes the pipeline and returns an associative array of the items, 
     * where the keys are determined by a classifier function.
     * Terminal operation.
     * 
     * classifier: fn ($item)
     * 
     * @param callable $classifier
     * @return array
     */
    public function groupBy(callable $classifier): array
    {
        $grouped = [];
        foreach ($this->source as $item) {
            $grouped[$classifier($item)][] = $item;
        }
        return $grouped;
    }

    /**
     * Consumes the pipeline and returns the first item which matches the predicate.
     * Short-circuiting terminal operation.
     * 
     * predicate: fn ($item)
     * 
     * @param callable $predicate
     * @return mixed
     */
    public function findFirst(callable $predicate): mixed
    {
        foreach ($this->source as $item) {
            if ($predicate($item)) return $item;
        }
        return null;
    }

    /**
     * Consumes the pipeline and returns true if any of the items matches the predicate.
     * Short-circuiting terminal operation.
     * 
     * predicate: fn ($item)
     * 
     * @param callable $predicate
     * @return bool
     */
    public function anyMatch(callable $predicate): bool
    {
        foreach ($this->source as $item) {
            if ($predicate($item)) return true;
        }
        return false;
    }

    /**
     * Consumes the pipeline and returns true if all of the items match the predicate.
     * Short-circuiting terminal operation.
     * 
     * predicate: fn ($item)
     * 
     * @param callable $predicate
     * @return bool
     */
    public function allMatch(callable $predicate): bool
    {
        foreach ($this->source as $item) {
            if (!$predicate($item)) return false;
        }
        return true;
    }

    /**
     * Consumes the pipeline and returns true if none of the items matches the predicate.
     * Short-circuiting terminal operation.
     * 
     * predicate: fn ($item)
     * 
     * @param callable $predicate
     * @return bool
     */
    public function noneMatch(callable $predicate): bool
    {
        foreach ($this->source as $item) {
            if ($predicate($item)) return false;
        }
        return true;
    }
}

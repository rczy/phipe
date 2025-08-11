<?php
namespace Rczy\Phipe\Operation;

use Generator;

trait Intermediate
{
    /**
     * Applies the mapper function to each item of the source.
     * Intermediate, lazy operation.
     * 
     * mapper: fn ($item)
     *
     * @param callable $mapper
     * @return Phipe
     */
    public function map(callable $mapper): self
    {
        $generator = function () use ($mapper): Generator {
            foreach ($this->source as $key => $item) {
                yield $key => $mapper($item);
            }
        };
        return new self($generator());
    }

    /**
     * Returns only the items that satisfy the predicate from the source.
     * Intermediate, lazy operation.
     * 
     * predicate: fn ($item)
     * 
     * @param callable $predicate
     * @return Phipe
     */
    public function filter(callable $predicate): self
    {
        $generator = function () use ($predicate): Generator {
            foreach ($this->source as $key => $item) {
                if ($predicate($item)) {
                    yield $key => $item;
                }
            }
        };
        return new self($generator());
    }

    /**
     * Returns each item of the source unchanged while performs the provided action.
     * Intermediate, lazy operation.
     * 
     * action: fn ($item)
     * 
     * @param callable $action
     * @return Phipe
     */
    public function peek(callable $action): self
    {
        $generator = function () use ($action): Generator {
            foreach ($this->source as $key => $item) {
                $action($item);
                yield $key => $item;
            }
        };
        return new self($generator());
    }

    /**
     * Returns only the first limited number of items from the source.
     * Intermediate, lazy operation.
     * 
     * @param int $limit
     * @return Phipe
     */
    public function limit(int $limit): self
    {
        $generator = function () use ($limit): Generator {
            $count = 0;
            foreach ($this->source as $key => $item) {
                if (++$count > $limit) break;
                yield $key => $item;
            }
        };
        return new self($generator());
    }

    /**
     * Skips the first specified number of items, then returns the rest from the source.
     * Intermediate, lazy operation.
     * 
     * @param int $skip
     * @return Phipe
     */
    public function skip(int $skip): self
    {
        $generator = function () use ($skip): Generator {
            if ($skip < 0) $skip = 0;
            foreach ($this->source as $key => $item) {
                if ($skip-- > 0) continue;
                yield $key => $item;
            }
        };
        return new self($generator());
    }

    /**
     * Returns the items from the source while the predicate is true.
     * Intermediate, lazy operation.
     * 
     * predicate: fn ($item)
     * 
     * @param callable $predicate
     * @return Phipe
     */
    public function takeWhile(callable $predicate): self
    {
        $generator = function () use ($predicate): Generator {
            foreach ($this->source as $key => $item) {
                if (!$predicate($item)) break;
                yield $key => $item;
            }
        };
        return new self($generator());
    }

    /**
     * Skips the first items of the source while the predicate is true, then returns the rest.
     * Intermediate, lazy operation.
     * 
     * predicate: fn ($item)
     * 
     * @param callable $predicate
     * @return Phipe
     */
    public function dropWhile(callable $predicate): self
    {
        $generator = function () use ($predicate): Generator {
            foreach ($this->source as $key => $item) {
                if ($predicate($item)) continue;
                yield $key => $item;
            }
        };
        return new self($generator());
    }

    /**
     * Changes the keys of the items by using the specified key mapper.
     * Intermediate, lazy operation.
     * 
     * keyMapper: fn ($key)
     * 
     * @param callable $keyMapper
     * @return Phipe
     */
    public function rekey(callable $keyMapper): self
    {
        $generator = function () use ($keyMapper): Generator {
            foreach ($this->source as $key => $item) {
                yield $keyMapper($key) => $item;
            }
        };
        return new self($generator());
    }

    /**
     * Returns only the keys of the items.
     * Intermediate, lazy operation.
     * 
     * @return Phipe
     */
    public function keys(): self
    {
        $generator = function (): Generator {
            foreach ($this->source as $key => $_) {
                yield $key;
            }
        };
        return new self($generator());
    }

    /**
     * Returns only the values of the items, discarding the original keys.
     * Intermediate, lazy operation.
     * 
     * @return Phipe
     */
    public function values(): self
    {
        $generator = function (): Generator {
            foreach ($this->source as $item) {
                yield $item;
            }
        };
        return new self($generator());
    }

    /**
     * Returns only the unique items of the source.
     * If a value mapper is provided, the uniqueness is determined by the result of that function.
     * Intermediate, lazy operation with a buffer.
     * 
     * valueMapper: fn ($item)
     * 
     * @param null|callable $valueMapper
     * @return Phipe
     */
    public function distinct(?callable $valueMapper = null): self
    {
        $generator = function () use ($valueMapper): Generator {
            $visited = [];
            foreach ($this->source as $key => $item) {
                $current = $valueMapper ? $valueMapper($item) : $item;
                if (in_array($current, $visited)) continue;
                $visited[] = $current;
                yield $key => $item;
            }
        };
        return new self($generator());
    }

    /**
     * Sorts the items with the help of the provided comparator, after the source is consumed.
     * Intermediate, eager operation.
     * 
     * comparator: fn ($item, $otherItem)
     * 
     * The comparator must return:
     *  - less than zero, if $item < $otherItem
     *  - zero, if $item == $otherItem
     *  - greater than zero, if $item > $otherItem
     * 
     * @param callable $comparator
     * @return Phipe
     */
    public function sort(callable $comparator): self
    {
        $sorted = [];
        foreach ($this->source as $key => $item) {
            $sorted[$key] = $item;
        }
        usort($sorted, $comparator);
        return new self($sorted);
    }

    /**
     * Sorts the items of the source in ascending order.
     * Intermediate, eager operation.
     * 
     * @return Phipe
     */
    public function asc(): self
    {
        return $this->sort(fn ($a, $b) => $a <=> $b);
    }

    /**
     * Sorts the items of the source in descending order.
     * Intermediate, eager operation.
     * 
     * @return Phipe
     */
    public function desc(): self
    {
        return $this->sort(fn ($a, $b) => -($a <=> $b));
    }

    /**
     * Reverses the order of the items after consuming the source.
     * Intermediate, eager operation.
     * 
     * @return Phipe
     */
    public function reverse(): self
    {
        $consumed = [];
        foreach ($this->source as $key => $item) {
            $consumed[$key] = $item;
        }
        $generator = function () use ($consumed): Generator {
            yield from array_reverse($consumed, true);
        };
        return new self($generator());
    }

    /**
     * Shuffles the order of the items after consuming the source.
     * Caution: Does not preserve keys.
     * Intermediate, eager operation.
     * 
     * @return Phipe
     */
    public function shuffle(): self
    {
        $consumed = [];
        foreach ($this->source as $key => $item) {
            $consumed[$key] = $item;
        }
        \shuffle($consumed);
        $generator = function () use ($consumed): Generator {
            yield from $consumed;
        };
        return new self($generator());
    }

    /**
     * Applies a predefined series of pipeline operations to the current pipeline.
     * This is useful for reusing a set of operations on different sources.
     * Intermediate operation.
     * 
     * chain: fn (Phipe $pipeline)
     * 
     * @param callable $chain
     * @return Phipe
     */
    public function apply(callable $chain): self
    {
        return $chain(new self($this->source));
    }
}

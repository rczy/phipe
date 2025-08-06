<?php
namespace Rczy\Phipe\Operation;

use Generator;

trait Intermediate
{
    public function map(callable $mapper): self
    {
        $generator = function () use ($mapper): Generator {
            foreach ($this->source as $key => $item) {
                yield $key => $mapper($item);
            }
        };
        return new self($generator());
    }

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

    public function rekey(callable $keyMapper): self
    {
        $generator = function () use ($keyMapper): Generator {
            foreach ($this->source as $key => $item) {
                yield $keyMapper($key) => $item;
            }
        };
        return new self($generator());
    }

    public function keys(): self
    {
        $generator = function (): Generator {
            foreach ($this->source as $key => $_) {
                yield $key;
            }
        };
        return new self($generator());
    }

    public function values(): self
    {
        $generator = function (): Generator {
            foreach ($this->source as $item) {
                yield $item;
            }
        };
        return new self($generator());
    }

    public function distinct(?callable $fieldMapper = null): self
    {
        $generator = function () use ($fieldMapper): Generator {
            $visited = [];
            foreach ($this->source as $key => $item) {
                $current = $fieldMapper ? $fieldMapper($item) : $item;
                if (in_array($current, $visited)) continue;
                $visited[] = $current;
                yield $key => $item;
            }
        };
        return new self($generator());
    }

    public function sort(callable $comparator): self
    {
        $sorted = [];
        foreach ($this->source as $key => $item) {
            $sorted[$key] = $item;
        }
        usort($sorted, $comparator);
        return new self($sorted);
    }

    public function asc(): self
    {
        return $this->sort(fn ($a, $b) => $a <=> $b);
    }

    public function desc(): self
    {
        return $this->sort(fn ($a, $b) => -($a <=> $b));
    }

    public function transform(callable $transformer): self
    {
        return $transformer(new self($this->source));
    }
}

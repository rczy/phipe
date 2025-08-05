<?php
namespace Rczy\Phipe\Operation;

use Generator;

trait Intermediate
{
    public function map(callable $mapper): self
    {
        $source = $this->source;
        $generator = function () use ($source, $mapper): Generator {
            foreach ($source as $key => $item) {
                yield $key => $mapper($item);
            }
        };
        return new self($generator());
    }

    public function filter(callable $predicate): self
    {
        $source = $this->source;
        $generator = function () use ($source, $predicate): Generator {
            foreach ($source as $key => $item) {
                if ($predicate($item)) {
                    yield $key => $item;
                }
            }
        };
        return new self($generator());
    }

    public function limit(int $limit): self
    {
        $source = $this->source;
        $generator = function () use ($source, $limit): Generator {
            $count = 0;
            foreach ($source as $key => $item) {
                if ($count++ > $limit) break;
                yield $key => $item;
            }
        };
        return new self($generator());
    }

    public function peek(callable $action): self
    {
        $source = $this->source;
        $generator = function () use ($source, $action): Generator {
            foreach ($source as $key => $item) {
                $action($item);
                yield $key => $item;
            }
        };
        return new self($generator());
    }

    public function transform(callable $transformer): self
    {
        return $transformer(new self($this->source));
    }
}

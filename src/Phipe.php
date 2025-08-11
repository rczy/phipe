<?php
namespace Rczy\Phipe;

use Generator;
use NoRewindIterator;

final class Phipe
{
    use Operation\Branching;
    use Operation\Intermediate;
    use Operation\Terminal;

    private function __construct(
        private iterable $source
    ) {}

    /**
     * Initializes a new pipeline instance with the provided source.
     * 
     * @param iterable $source
     * @return Phipe
     */
    public static function from(iterable $source): self
    {
        $generator = function () use ($source): Generator {
            yield from $source;
        };
        return new self(new NoRewindIterator($generator()));
    }

    /**
     * Consumes the next item of the source and returns it as a key-value pair,
     * or null if the source is exhausted.
     * 
     * Caution: This method is primarily for internal use.
     * For most use cases it is recommended to use higher-level functions provided by Phipe.
     * 
     * @return array|null
     */
    public function consume(): ?array
    {
        if ($this->source->valid()) {
            $key = $this->source->key();
            $value = $this->source->current();
            $this->source->next();
            return [$key, $value];
        }
        return null;
    }
}

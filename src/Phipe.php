<?php
namespace Rczy\Phipe;

final class Phipe
{
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
        return new self($source);
    }
}

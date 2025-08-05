<?php
namespace Rczy\Phipe;

final class Phipe
{
    use Operation\Intermediate;
    use Operation\Terminal;

    private function __construct(
        private iterable $source
    ) {}

    public static function from(iterable $source): self
    {
        return new self($source);
    }
}

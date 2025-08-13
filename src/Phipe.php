<?php
namespace Rczy\Phipe;

use Generator;
use NoRewindIterator;
use RuntimeException;

final class Phipe
{
    use Operation\Branching;
    use Operation\Intermediate;
    use Operation\Terminal;

    private static array $extensions = [];

    private function __construct(
        private iterable $source
    ) {}

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
     * Extends the Phipe class with a custom operation.
     * 
     * The provided callable is bound to the Phipe object, allowing
     * it to access the pipeline's internal state via '$this'.
     * 
     * @param string $name
     * @param callable $function
     * @return void
     */
    public static function extend(string $name, callable $function): void
    {
        static::$extensions[$name] = $function;
    }

    /**
     * Magic method to dynamically call custom operations
     * registered via the 'extend' method on the Phipe instance.
     * 
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call(string $name, array $args): mixed
    {
        if (!$function = static::$extensions[$name]) {
            throw new RuntimeException(
                "'$name' operation is not found. Use the 'extend' static method on the Phipe class to add custom operations."
            );
        }
        return call_user_func_array($function->bindTo($this, static::class), $args);
    }
}

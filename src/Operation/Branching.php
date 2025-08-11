<?php
namespace Rczy\Phipe\Operation;

use Generator;
use Rczy\Phipe\Phipe;

trait Branching
{
    /**
     * Splits the pipeline into multiple independent branches.
     * 
     * This is a buffered operation. The first branch consumes from the original
     * source, while subsequent branches are supplied from a shared buffer.
     * 
     * Caution: The original pipeline should not be used after this operation.
     * Any terminal operation on the original pipeline will consume all the remaining
     * items, preventing the teed branches from accessing them.
     * 
     * Does not preserve keys.
     * 
     * @param int $branches
     * @return array
     */
    public function tee(int $branches = 2): array
    {
        if ($branches < 2) $branches = 2;

        $buffer = [];
        $generator = function () use (&$buffer): Generator {
            $cursor = 0;
            for (;;) {
                if ($cursor < count($buffer)) {
                    yield $buffer[$cursor];
                } else {
                    if ($this->source->valid()) {
                        $value = $this->source->current();
                        $buffer[] = $value;
                        $this->source->next();
                        yield $value;
                    } else {
                        return;
                    }
                }
                $cursor++;
            }
        };

        $pipelines = [];
        for ($i = 0; $i < $branches; $i++) {
            $pipelines[] = new self($generator());
        }

        return $pipelines;
    }

    /**
     * Appends one or more pipelines to the current pipeline.
     * The new pipeline will consume items from the current pipeline first,
     * followed sequentially by the items from each appended pipeline.
     * 
     * Caution: Does not preserve keys.
     * 
     * @param Phipe ...$pipelines
     * @return Phipe
     */
    public function append(Phipe ...$pipelines): self
    {
        $generator = function () use ($pipelines): Generator {
            foreach ($this->source as $item) {
                yield $item;
            }
            foreach ($pipelines as $pipeline) {
                while ([$_, $item] = $pipeline->consume()) {
                    yield $item;
                }
            }
        };
        return new self($generator());
    }

    /**
     * Interleaves items from the current pipeline with items from one or more other pipelines into tuples.
     * If the pipelines are of different lengths, it stops when the shortest pipeline is consumed.
     * 
     * Caution: Does not preserve keys.
     * 
     * @param Phipe ...$pipelines
     * @return Phipe
     */
    public function zip(Phipe ...$pipelines): self
    {
        $generator = function () use ($pipelines): Generator {
            while ($this->source->valid()) {
                $zipped = [];
                [$key, $value] = $this->consume();
                $zipped[$key][] = $value;
                foreach ($pipelines as $pipeline) {
                    if (![$_, $value] = $pipeline->consume()) {
                        return;
                    }
                    $zipped[$key][] = $value;
                }
                yield $zipped[$key];
            }
        };
        return new self($generator());
    }
}

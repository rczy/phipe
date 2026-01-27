<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Phipe;

final class PipelineTest extends TestCase
{
    use AssertionExtensions;

    public function testItWorksWithInfiniteGenerators(): void
    {
        $infinite = function () {
            $i = 0;
            for(;;) yield $i++;
        };

        $result = Phipe::from($infinite())
            ->filter(fn ($x) => $x % 2 === 0)
            ->skip(10)
            ->limit(2)
            ->toArray();

        $this->assertSameArrayValues([20, 22], $result);
    }

    public function testItChainsMapFilterAndReduce(): void
    {
    }

    public function testItComplexBranchingAndMerging(): void
    {
    }

    public function testItHandlesExceptionsInPipeline(): void
    {
    }

    public function testItReusesPipelineDefinition(): void
    {
    }

    public function testItSortsAfterFilteringAndMapping(): void
    {
    }
}

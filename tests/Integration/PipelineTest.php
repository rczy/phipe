<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\Configuration\Php;
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
        $data = range(1, 10);

        $result = Phipe::from($data)
            ->map(fn ($x) => $x * 10)
            ->filter(fn ($x) => $x < 50)
            ->reduce(0, fn ($accumulator, $item) => $accumulator += $item);

        $this->assertEquals(100, $result);
    }

    public function testItHandlesComplexBranchingAndMerging(): void
    {
        $data = range(1, 10);

        [$even, $odd] = Phipe::from($data)->tee();

        $result = $even->filter(fn ($x) => $x % 2 === 0)
            ->zip(
                $odd->filter(fn ($x) => $x % 2 === 1)
            )
            ->toArray();

        $this->assertSame([[2, 1], [4, 3], [6, 5], [8, 7], [10, 9]], $result);
    }

    public function testItConveysExceptionsInPipeline(): void
    {
        $pipeline = Phipe::from([1])->map(fn () => throw new \Exception());

        $this->expectException(\Exception::class);
        $pipeline->toArray();
    }

    public function testItReusesPipelineDefinition(): void
    {
        $reusableLogic = function (Phipe $pipeline) {
            return $pipeline
                ->filter(fn ($x) => $x >= 10 && $x < 20)
                ->desc();
        };

        $result1 = Phipe::from([1, 11, 23, 4, 19])->apply($reusableLogic)->join();
        $result2 = Phipe::from([42, 10, 3, 16, 9])->apply($reusableLogic)->join();

        $this->assertSame("1911", $result1);
        $this->assertSame("1610", $result2);
    }
}

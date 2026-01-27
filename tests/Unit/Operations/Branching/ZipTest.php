<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Phipe;

final class ZipTest extends TestCase
{
    public function testItZipsTwoPipelinesOfEqualLength(): void
    {
        $pipeline1 = Phipe::from([1, 2, 3]);
        $pipeline2 = Phipe::from([4, 5, 6]);

        $result = $pipeline1->zip($pipeline2)->toArray();

        $this->assertSame([[1, 4], [2, 5], [3, 6]], $result);
    }

    public function testItStopsWhenTheShortestPipelineEnds(): void
    {
        $pipeline1 = Phipe::from([1, 2, 3]);
        $pipeline2 = Phipe::from([4, 5]);

        $result = $pipeline1->zip($pipeline2)->toArray();

        $this->assertSame([[1, 4], [2, 5]], $result);
    }

    public function testItStopsWhenTheSourcePipelineIsTheShortest(): void
    {
        $pipeline1 = Phipe::from([1, 2, 3]);
        $pipeline2 = Phipe::from([4, 5, 6, 7]);

        $result = $pipeline1->zip($pipeline2)->toArray();

        $this->assertSame([[1, 4], [2, 5], [3, 6]], $result);
    }

    public function testItReturnsEmptyIfAnyPipelineIsEmpty(): void
    {
        $pipeline1 = Phipe::from([1, 2, 3]);
        $pipeline2 = Phipe::from([]);

        $result = $pipeline1->zip($pipeline2)->toArray();

        $this->assertSame([], $result);
    }

    public function testItHandlesVariadicPipelines(): void
    {
        $pipeline1 = Phipe::from([1, 2]);
        $pipeline2 = Phipe::from([3, 4]);
        $pipeline3 = Phipe::from([5, 6]);
        $pipeline4 = Phipe::from([7, 8]);

        $result = $pipeline1
            ->zip($pipeline2, $pipeline3, $pipeline4)
            ->toArray();

        $this->assertSame([[1, 3, 5, 7], [2, 4, 6, 8]], $result);
    }

    public function testItDoesNotPreserveOriginalKeys(): void
    {
        $pipeline1 = Phipe::from([1, 2]);
        $pipeline2 = Phipe::from(['a' => 4, 'b' => 5]);

        $result = $pipeline1->zip($pipeline2)->toArray();

        $this->assertSame([[1, 4], [2, 5]], $result);
    }
}

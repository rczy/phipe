<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Phipe;

final class AppendTest extends TestCase
{
    public function testItConcatenatesSequentially(): void
    {
        $pipeline1 = Phipe::from([1, 2]);
        $pipeline2 = Phipe::from([3, 4]);

        $result = $pipeline1->append($pipeline2)->toArray();

        $this->assertSame([1, 2, 3, 4], $result);
    }

    public function testItSupportsVariadicArguments(): void
    {
        $pipeline1 = Phipe::from([1, 2]);
        $pipeline2 = Phipe::from([3, 4]);
        $pipeline3 = Phipe::from([5, 6]);
        $pipeline4 = Phipe::from([7, 8]);

        $result = $pipeline1
            ->append($pipeline2, $pipeline3, $pipeline4)
            ->toArray();

        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8], $result);
    }

    public function testItResetsKeys(): void
    {
        $pipeline1 = Phipe::from([1, 2]);
        $pipeline2 = Phipe::from(['a' => 3, 'b' => 4]);

        $result = $pipeline1->append($pipeline2)->toArray();

        $this->assertSame([1, 2, 3, 4], $result);
    }

    public function testItHandlesEmptySource(): void
    {
        $pipeline1 = Phipe::from([]);
        $pipeline2 = Phipe::from([1, 2]);
        $pipeline3 = Phipe::from([]);

        $result = $pipeline1
            ->append($pipeline2, $pipeline3)
            ->toArray();

        $this->assertSame([1, 2], $result);
    }

    public function testItMaintainsLaziness(): void
    {
        $pipeline = Phipe::from([1, 2, 3]);
        $explodingPipeline = Phipe::from((function () {
            yield 4;
            throw new \Exception();
        })());
        $pipeline = $pipeline->append($explodingPipeline);

        $pipeline->consume();
        $pipeline->consume();

        // Consuming yields the current element and moves forward to the next position,
        // therefore the exception is triggered one step earlier (lookahead).
        $this->expectException(\Exception::class);
        $pipeline->consume();
    }
}

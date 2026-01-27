<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Phipe;

final class SortingTest extends TestCase
{
    use AssertionExtensions;

    public function testItSortsInAscendingOrder(): void
    {
        $data = [3, 1, 4, 2];

        $asc = Phipe::from($data)
            ->asc()
            ->toArray();

        $this->assertSameArrayValues([1, 2, 3, 4], $asc);
    }

    public function testItSortsInDescendingOrder(): void
    {
        $data = [3, 1, 4, 2];

        $desc = Phipe::from($data)
            ->desc()
            ->toArray();

        $this->assertSameArrayValues([4, 3, 2, 1], $desc);
    }

    public function testItReversesOrderCorrectly(): void
    {
        $data = [3, 1, 4, 2];

        $reversed = Phipe::from($data)
            ->reverse()
            ->toArray();

        $this->assertSameArrayValues([2, 4, 1, 3], $reversed);
    }

    public function testItSortsWithCustomComparator(): void
    {
        $data = [3, 1, 4, 2];

        $custom = Phipe::from($data)
            ->sort(fn ($a, $b) => $a <=> $b)
            ->toArray();

        $this->assertSameArrayValues([1, 2, 3, 4], $custom);
    }

    public function testItShufflesElements(): void
    {
        $data = range(1, 20);
        $sum = array_sum($data);

        $result = Phipe::from($data)
            ->shuffle()
            ->toArray();

        $this->assertCount(20, $result);
        $this->assertEquals($sum, array_sum($result));
        $this->assertNotSameArrayValues($data, $result);

        sort($result);
        $this->assertSameArrayValues($data, $result);
    }
}

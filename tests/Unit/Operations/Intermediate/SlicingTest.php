<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Phipe;

final class SlicingTest extends TestCase
{
    use AssertionExtensions;

    public function testItHandlesLimit(): void
    {
        $data = range(1, 10);

        $result = Phipe::from($data)
            ->limit(2)
            ->toArray();

        $this->assertSameArrayValues([1, 2], $result);
    }

    public function testItHandlesSkip(): void
    {
        $data = range(1, 10);

        $result = Phipe::from($data)
            ->skip(5)
            ->toArray();

        $this->assertSameArrayValues([6, 7, 8, 9, 10], $result);
    }

    public function testItHandlesConditionalTake(): void
    {
        $data = [1, 2, 10, 3, 4];
        $condition = fn ($x) => $x < 10;

        $result = Phipe::from($data)
            ->takeWhile($condition)
            ->toArray();

        $this->assertSameArrayValues([1, 2], $result);
    }

    public function testItHandlesConditionalDrop(): void
    {
        $data = [1, 2, 10, 3, 4];
        $condition = fn ($x) => $x < 10;

        $result = Phipe::from($data)
            ->dropWhile($condition)
            ->toArray();

        $this->assertSameArrayValues([10, 3, 4], $result);
    }
}

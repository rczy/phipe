<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Phipe;

final class FilteringTest extends TestCase
{
    use AssertionExtensions;

    public function testItCanFilterValues(): void
    {
        $data = [1, 2, 3, 4];

        $result = Phipe::from($data)
            ->filter(fn ($x) => $x % 2 === 0)
            ->toArray();

        $this->assertSameArrayValues([2, 4], $result);
    }

    public function testItFiltersDistinctValues(): void
    {
        $data = [1, 2, 2, 3, 3, 3];

        $result = Phipe::from($data)
            ->distinct()
            ->toArray();

        $this->assertSameArrayValues([1, 2, 3], $result);
    }

    public function testItFiltersDistinctValuesWithMapper(): void
    {
        $data = ['a', 'bb', 'cc', 'ddd'];

        $result = Phipe::from($data)
            ->distinct(fn ($w) => strlen($w))
            ->toArray();

        $this->assertSameArrayValues(['a', 'bb', 'ddd'], $result);
    }
}

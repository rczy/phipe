<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Phipe;

final class ReductionTest extends TestCase
{
    public function testItReducesStreamToSingleValue(): void
    {
        $data = [1, 2, 3, 4];

        $result = Phipe::from($data)
            ->reduce(0, fn ($accumulator, $item, $key) => $accumulator += ($item + $key));

        $this->assertIsInt($result);
        $this->assertEquals(16, $result);
    }

    public function testItCountsItems(): void
    {
        $data = [1, 2, 3, 4];

        $result = Phipe::from($data)->count();

        $this->assertIsInt($result);
        $this->assertEquals(4, $result);
    }

    public function testItSumsValues(): void
    {
        $data = [1, 2, 3, 4];

        $result = Phipe::from($data)->sum();

        $this->assertIsInt($result);
        $this->assertEquals(10, $result);
    }

    public function testItSumsValuesWithValueMapper(): void
    {
        $data = [
            ['name' => 'a', 'value' => 1],
            ['name' => 'b', 'value' => 2],
            ['name' => 'c', 'value' => 3],
            ['name' => 'd', 'value' => 4],
        ];

        $result = Phipe::from($data)
            ->sum(fn ($item) => $item['value']);

        $this->assertIsInt($result);
        $this->assertEquals(10, $result);
    }

    public function testItCalculatesAverage(): void
    {
        $data = [1, 2, 3, 4];

        $result = Phipe::from($data)->avg();

        $this->assertIsFloat($result);
        $this->assertEquals(2.5, $result);
    }

    public function testItCalculatesAverageWithValueMapper(): void
    {
        $data = [
            ['name' => 'a', 'value' => 1],
            ['name' => 'b', 'value' => 2],
            ['name' => 'c', 'value' => 3],
            ['name' => 'd', 'value' => 4],
        ];

        $result = Phipe::from($data)
            ->avg(fn ($item) => $item['value']);

        $this->assertIsFloat($result);
        $this->assertEquals(2.5, $result);
    }

    public function testItFindsMin(): void
    {
        $data = [1, 2, 3, 4];

        $result = Phipe::from($data)->min();

        $this->assertIsInt($result);
        $this->assertEquals(1, $result);
    }

    public function testItFindsMinWithValueMapper(): void
    {
        $data = [
            ['name' => 'a', 'value' => 1],
            ['name' => 'b', 'value' => 2],
            ['name' => 'c', 'value' => 3],
            ['name' => 'd', 'value' => 4],
        ];

        $result = Phipe::from($data)
            ->min(fn ($item) => $item['value']);

        $this->assertIsInt($result);
        $this->assertEquals(1, $result);
    }

    public function testItFindsMax(): void
    {
        $data = [1, 2, 3, 4];

        $result = Phipe::from($data)->max();

        $this->assertIsInt($result);
        $this->assertEquals(4, $result);
    }

    public function testItFindsMaxWithValueMapper(): void
    {
        $data = [
            ['name' => 'a', 'value' => 1],
            ['name' => 'b', 'value' => 2],
            ['name' => 'c', 'value' => 3],
            ['name' => 'd', 'value' => 4],
        ];

        $result = Phipe::from($data)
            ->max(fn ($item) => $item['value']);

        $this->assertIsInt($result);
        $this->assertEquals(4, $result);
    }

    public function testItJoinsItemsToString(): void
    {
        $data = [1, 2, 3, 4];

        $result = Phipe::from($data)->join();

        $this->assertIsString($result);
        $this->assertEquals("1234", $result);
    }

    public function testItJoinsItemsToStringWithSeparator(): void
    {
        $data = [1, 2, 3, 4];

        $result = Phipe::from($data)->join(', ');

        $this->assertIsString($result);
        $this->assertEquals("1, 2, 3, 4", $result);
    }
}

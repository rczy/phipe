<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Phipe;

final class TransformationTest extends TestCase
{
    use AssertionExtensions;

    public function testItCanMapValues(): void
    {
        $data = [1, 2, 3, 4];

        $result = Phipe::from($data)
            ->map(fn ($x) => $x * 3)
            ->toArray();

        $this->assertSameArrayValues([3, 6, 9, 12], $result);
    }

    public function testItLeavesDataUnchangedWhenPeeks(): void
    {
        $data = [1, 2, 3, 4];
        $observed = [];

        $pipeline = Phipe::from($data)
            ->peek(function ($x) use (&$observed) {
                $observed[] = $x * 10;
            });

        $this->assertSame([], $observed);

        $result = $pipeline->toArray();

        $this->assertSameArrayValues($data, $result);
        $this->assertSameArrayValues([10, 20, 30, 40], $observed);
    }

    public function testItManipulatesKeys(): void
    {
        $data = ['id_1' => 'Alice', 'id_2' => 'Bob'];

        $rekeyed = Phipe::from($data)
            ->rekey(fn ($k) => ((int)str_replace('id_', '', $k)) * 10)
            ->toArray();

        $this->assertSame([10 => 'Alice', 20 => 'Bob'], $rekeyed);
    }

    public function testItCanGetKeys(): void
    {
        $data = ['id_1' => 'Alice', 'id_2' => 'Bob'];

        $keys = Phipe::from($data)
            ->keys()
            ->toArray();

        $this->assertSame(['id_1', 'id_2'], $keys);
    }

    public function testItCanGetValues(): void
    {
        $data = ['id_1' => 'Alice', 'id_2' => 'Bob'];

        $values = Phipe::from($data)
            ->values()
            ->toArray();

        $this->assertSame(['Alice', 'Bob'], $values);
    }

    public function testItReusesPipelineViaApply(): void
    {
        $pipeline1 = Phipe::from([1, 2, 3]);
        $pipeline2 = Phipe::from([4, 5]);

        $double = function (Phipe $pipeline) {
            return $pipeline
                ->map(fn ($x) => $x * 2);
        };

        $result1 = $pipeline1->apply($double)->toArray();
        $result2 = $pipeline2->apply($double)->toArray();

        $this->assertSameArrayValues([2, 4, 6], $result1);
        $this->assertSameArrayValues([8, 10], $result2);
    }
}

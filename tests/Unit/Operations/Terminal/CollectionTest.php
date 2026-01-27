<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Phipe;

final class CollectionTest extends TestCase
{
    public function testItCollectsItemsToArray(): void
    {
        $generator = function () {
            yield 1;
            yield 2;
            yield 3;
        };

        $result = Phipe::from($generator())->toArray();

        $this->assertIsArray($result);
        $this->assertSame([1, 2, 3], $result);
    }

    public function testItGroupsItemsByClassifier(): void
    {
        $data = range(1, 10);

        $result = Phipe::from($data)
            ->groupBy(fn ($item) => ($item % 2 === 0) ? 'even' : 'odd');

        $this->assertSame(['odd' => [1, 3, 5, 7, 9], 'even' => [2, 4, 6, 8, 10]], $result);
    }
}

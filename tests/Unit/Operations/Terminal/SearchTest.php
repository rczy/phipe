<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Phipe;

final class SearchTest extends TestCase
{
    public function testItFindsFirstItem(): void
    {
        $data = [1, 2, 10, 3, 4];

        $result = Phipe::from($data)->first();
        
        $this->assertEquals(1, $result);
    }

    public function testItFindsFirstItemMatchingPredicate(): void
    {
        $data = [1, 2, 10, 3, 4];
        $predicate = fn ($item) => $item >= 3;

        $result = Phipe::from($data)
            ->first($predicate);
        
        $this->assertEquals(10, $result);
    }

    public function testItReturnsNullAsFirstIfSourceIsEmpty(): void
    {
        $data = [];

        $result = Phipe::from($data)->first();

        $this->assertNull($result);
    }

    public function testItReturnsNullIfNoFirstMatch(): void
    {
        $data = [1, 2, 10, 3, 4];
        $predicate = fn ($item) => $item > 10;

        $result = Phipe::from($data)
            ->first($predicate);
        
        $this->assertNull($result);
    }

    public function testItReturnsLastItem(): void
    {
        $data = [1, 2, 10, 3, 4];

        $result = Phipe::from($data)->last();
        
        $this->assertEquals(4, $result);
    }

    public function testItFindsLastItemMatchingPredicate(): void
    {
        $data = [1, 2, 10, 3, 4];
        $predicate = fn ($item) => $item <= 3;

        $result = Phipe::from($data)
            ->last($predicate);
        
        $this->assertEquals(3, $result);
    }

    public function testItReturnsNullAsLastIfSourceIsEmpty(): void
    {
        $data = [];

        $result = Phipe::from($data)->last();

        $this->assertNull($result);
    }

    public function testItReturnsNullIfNoLastMatch(): void
    {
        $data = [1, 2, 10, 3, 4];
        $predicate = fn ($item) => $item > 10;

        $result = Phipe::from($data)
            ->last($predicate);
        
        $this->assertNull($result);
    }

    public function testItChecksIfAnyMatchPredicate(): void
    {
        $data = [1, 2, 10, 3, 4];
        $predicate = fn ($item) => $item >= 10;

        $result = Phipe::from($data)
            ->any($predicate);
        
        $this->assertTrue($result);
    }

    public function testItChecksIfAllMatchPredicate(): void
    {
        $data = [1, 2, 10, 3, 4];
        $predicate = fn ($item) => $item >= 10;

        $result = Phipe::from($data)
            ->all($predicate);
        
        $this->assertFalse($result);
    }

    public function testItChecksIfNoneMatchPredicate(): void
    {
        $data = [1, 2, 10, 3, 4];
        $predicate = fn ($item) => $item >= 10;

        $result = Phipe::from($data)
            ->none($predicate);
        
        $this->assertFalse($result);
    }
}

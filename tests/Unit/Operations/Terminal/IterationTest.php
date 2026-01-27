<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Phipe;

final class IterationTest extends TestCase
{
    public function testItIteratesOverEveryItem(): void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = [];

        $returned = Phipe::from($data)
            ->forEach(function ($item, $key) use (&$result) {
                $result[$key] = $item;
            });

        $this->assertNull($returned);
        $this->assertSame($data, $result);
    }
}

<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Exceptions\UnknownOperationException;
use Rczy\Phipe\Phipe;

final class PhipeCoreTest extends TestCase
{
    public function testItCreatesPipelineFromArray(): void
    {
        $data = [1];
        
        $pipeline = Phipe::from($data);
        [$_, $item] = $pipeline->consume();

        $this->assertInstanceOf(Phipe::class, $pipeline);
        $this->assertEquals(1, $item);
    }

    public function testItCreatesPipelineFromGenerator(): void
    {
        $generator = function () {
            yield 1;
        };
        
        $pipeline = Phipe::from($generator());
        [$_, $item] = $pipeline->consume();

        $this->assertInstanceOf(Phipe::class, $pipeline);
        $this->assertEquals(1, $item);
    }

    public function testItConsumesSourceRespectingKeys(): void
    {
        $data = ['a' => 1];

        $pipeline = Phipe::from($data);
        [$key, $item] = $pipeline->consume();

        $this->assertEquals('a', $key);
        $this->assertEquals(1, $item);
    }

    public function testItThrowsExceptionIfMethodNotFound(): void
    {
        $this->expectException(UnknownOperationException::class);
        Phipe::from([])->unknown();
    }

    public function testItExtendsPipelineWithCustomMethod(): void
    {
        Phipe::extend("toJson", function () {
            /** @var Phipe $this */
            return json_encode($this->toArray());
        });

        $data = [1, 2];

        $result = Phipe::from($data)->toJson();

        $this->assertSame(json_encode($data), $result);
    }

    public function testItAllowsChainingOfExtendedMethods(): void
    {
        Phipe::extend("multiply", function (int $multiplier) {
            $generator = function () use ($multiplier) {
            /** @var Phipe $this */
            foreach ($this->source as $item) {
                    yield $item * $multiplier;
                }
            };
            return new Phipe($generator());
        });

        Phipe::extend("divide", function (int $divisor) {
            $generator = function () use ($divisor) {
            /** @var Phipe $this */
            foreach ($this->source as $item) {
                    yield $item / $divisor;
                }
            };
            return new Phipe($generator());
        });

        $data = [3, 6, 12];

        $result = Phipe::from($data)
            ->multiply(4)
            ->divide(3)
            ->toArray();

        $this->assertSame([4, 8, 16], $result);
    }
}

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
    }

    public function testItPassesContextToExtendedMethod(): void
    {
    }

    public function testItAllowsChainingOfExtendedMethods(): void
    {
    }
}

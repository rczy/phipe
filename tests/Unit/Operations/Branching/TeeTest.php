<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Rczy\Phipe\Phipe;

final class TeeTest extends TestCase
{
    public function testItSplitsIntoTwoBranchesByDefault(): void
    {
        $branches = Phipe::from([])->tee();

        $this->assertCount(2, $branches);
    }

    public function testItDefaultsToTwoBranchesIfLessThanTwoIsRequested(): void
    {
        $one = Phipe::from([])->tee(1);
        $zero = Phipe::from([])->tee(0);
        $negative = Phipe::from([])->tee(-1);

        $this->assertCount(2, $one);
        $this->assertCount(2, $zero);
        $this->assertCount(2, $negative);
    }

    public function testItSplitsIntoSpecifiedNumberOfBranches(): void
    {
        $numOfBranches = 10;
        $branches = Phipe::from([])->tee($numOfBranches);

        $this->assertCount($numOfBranches, $branches);
    }

    public function testItHandlesEmptySource(): void
    {
        $data = [];
        [$branch1, $branch2] = Phipe::from($data)->tee();

        $this->assertSame($data, $branch1->toArray());
        $this->assertSame($data, $branch2->toArray());
    }

    public function testItAllowsFullConsumptionOfOneBranchBeforeStartingAnother(): void
    {
        $data = range(5, 10);
        [$branch1, $branch2] = Phipe::from($data)->tee();

        $consumed = $branch1->toArray();
        [$_, $first] = $branch2->consume();

        $this->assertSame($data, $consumed);
        $this->assertEquals($data[0], $first);
    }

    public function testItSupportsInterleavedConsumptionAccrossBranches(): void
    {
        $data = range(5, 10);
        [$branch1, $branch2] = Phipe::from($data)->tee();

        [$_, $branch1First] = $branch1->consume();
        [$_, $branch2First] = $branch2->consume();
        [$_, $branch2Second] = $branch2->consume();
        [$_, $branch1Second] = $branch1->consume();

        $this->assertEquals($data[0], $branch1First);
        $this->assertEquals($data[0], $branch2First);
        $this->assertEquals($data[1], $branch2Second);
        $this->assertEquals($data[1], $branch1Second);
    }
}

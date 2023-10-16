<?php

namespace Tests\Unit\Domain\WaterLevel\Aggregator;

use App\Domain\WaterLevel\Aggregator\WaterAggregator;
use App\Domain\WaterLevel\Entity\ValueObject\WaterType;
use App\Domain\WaterLevel\Entity\Water;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;

/**
 * @group water-level
 */
class WaterAggregatorTest extends TestCase
{
    public function testAggregateByFirstLetter(): void
    {
        $aggregator = new WaterAggregator();

        $aggregatedWaters = $aggregator->aggregateByFirstLetter($this->getWaters());

        $countOfDifferentFirstLettersInWaterNames = 3;

        $this->assertCount($countOfDifferentFirstLettersInWaterNames, $aggregatedWaters);

        foreach ($aggregatedWaters as $watersByFirstLetter) {
            foreach ($watersByFirstLetter->getWaters() as $water) {
                $firstLetter = mb_substr($water->getName(), 0, 1);
                $this->assertEquals($firstLetter, $watersByFirstLetter->getFirstLetter());
            }
        }
    }

    /**
     * @return Water[]
     */
    private function getWaters(): array
    {
        return [
            $this->createWater('Обь'),
            $this->createWater('Ока'),
            $this->createWater('Волга'),
            $this->createWater('Кама'),
        ];
    }

    private function createWater(string $name): Water
    {
        return new Water(Uuid::uuid4(), 'some_slug', $name, WaterType::river());
    }
}

<?php

namespace Tests\Functional\Domain\WaterLevel\Repository;

use App\Domain\WaterLevel\Entity\ValueObject\WaterType;
use App\Domain\WaterLevel\Entity\Water;
use App\Domain\WaterLevel\Repository\WaterRepository;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdskGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdWater;
use Tests\DataFixtures\ORM\WaterLevel\LoadHideBerdskGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadObWater;
use Tests\Functional\TestCase;

/**
 * @group water-level
 */
class WaterRepositoryTest extends TestCase
{
    private WaterRepository $waterRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            LoadObWater::class,
            LoadBerdWater::class,
            LoadBerdskGaugingStation::class,
            LoadNovosibirskGaugingStation::class,
        ]);

        $this->waterRepository = $this->getContainer()->get(WaterRepository::class);
    }

    protected function tearDown(): void
    {
        unset($this->waterRepository);

        parent::tearDown();
    }

    /**
     * @dataProvider getQueriesByWaterSearch
     *
     * @param string[] $expectedWaterNames
     */
    public function testFindForAutocompleteOneWater(string $query, array $expectedWaterNames): void
    {
        $waters = $this->waterRepository->findForAutocomplete($query);

        $this->assertCount(count($expectedWaterNames), $waters);

        foreach ($waters as $water) {
            $this->assertContains($water->getName(), $expectedWaterNames);
        }
    }

    public function testFindAllOrderByName(): void
    {
        $watersOrderedByName = $this->waterRepository->findAllOrderByName();
        $firstWaterName = $watersOrderedByName[0]->getName();
        $secondWaterName = $watersOrderedByName[1]->getName();

        $this->assertTrue(strcasecmp($firstWaterName, $secondWaterName) < 0);
    }

    public function testFindForAutocompleteWaterWithoutGaugingStations(): void
    {
        $this->loadFixtures([
            LoadObWater::class,
            LoadBerdWater::class,
            LoadHideBerdskGaugingStation::class,
            LoadNovosibirskGaugingStation::class,
        ]);

        $waters = $this->waterRepository->findForAutocomplete('б');

        $this->assertCount(1, $waters);
        $this->assertEquals('Обь', $waters[0]->getName());
    }

    /**
     * @return string[]
     */
    public function getQueriesByWaterSearch(): array
    {
        return [
            [
                'query' => 'о',
                'expectedWaterNames' => [
                    'Обь',
                ],
            ],
            [
                'query' => 'б',
                'expectedWaterNames' => [
                    'Обь',
                    'Бердь',
                ],
            ],
        ];
    }
}

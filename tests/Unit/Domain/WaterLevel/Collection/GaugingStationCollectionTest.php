<?php

namespace Tests\Unit\Domain\WaterLevel\Collection;

use App\Domain\WaterLevel\Collection\GaugingStationCollection;
use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Domain\WaterLevel\Entity\ValueObject\WaterType;
use App\Domain\WaterLevel\Entity\Water;
use App\Domain\WaterLevel\Exception\GaugingStationDistanceFromSourceNotDefinedException;
use App\Util\Coordinates\Coordinates;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;

/**
 * @group water-level
 */
class GaugingStationCollectionTest extends TestCase
{
    public function testFindAllCloserToSourceOf(): void
    {
        $obWater = $this->createWater('Обь');
        $obskoeVodohranWater = $this->createWater('Обское водохранилище');
        $obskoeVodohranWater->setParentWater($obWater);
        $obskoeVodohranWater->setDistanceFromParentWaterSourceInKilometers(150);

        $barnaulGaugingStation = $this->createGaugingStationOnDistanceFromSource('Барнаул', $obWater, 100);
        $berdskGaugingStation = $this->createGaugingStationOnDistanceFromSource('Бердск', $obskoeVodohranWater, 50);
        $novosibirskGaugingStation = $this->createGaugingStationOnDistanceFromSource('Новосибирск', $obWater, 210);

        $gaugingStations = new GaugingStationCollection([
            $novosibirskGaugingStation,
            $barnaulGaugingStation,
            $berdskGaugingStation,
        ]);

        $stationsCloserToSource = $gaugingStations->findAllCloserToSourceOf($novosibirskGaugingStation);

        $this->assertCount(2, $stationsCloserToSource);

        $novosibirskGaugingStationDistanceFromSource = $novosibirskGaugingStation->getDistanceFromSource();

        foreach ($stationsCloserToSource as $stationCloserToSource) {
            /** @var GaugingStation $stationCloserToSource */
            $this->assertTrue($stationCloserToSource->getDistanceFromSourceOfAncestorWater($obWater) < $novosibirskGaugingStationDistanceFromSource);
        }
    }

    public function testFindAllFurtherFromSourceOf(): void
    {
        $obWater = $this->createWater('Обь');
        $obskoeVodohranWater = $this->createWater('Обское водохранилище');
        $obskoeVodohranWater->setParentWater($obWater);
        $obskoeVodohranWater->setDistanceFromParentWaterSourceInKilometers(150);

        $barnaulGaugingStation = $this->createGaugingStationOnDistanceFromSource('Барнаул', $obWater, 100);
        $berdskGaugingStation = $this->createGaugingStationOnDistanceFromSource('Бердск', $obskoeVodohranWater, 50);
        $novosibirskGaugingStation = $this->createGaugingStationOnDistanceFromSource('Новосибирск', $obWater, 210);

        $gaugingStations = new GaugingStationCollection([
            $novosibirskGaugingStation,
            $barnaulGaugingStation,
            $berdskGaugingStation,
        ]);

        $stationsFurtherFromSource = $gaugingStations->findAllFurtherFromSourceOf($barnaulGaugingStation);

        $this->assertCount(2, $stationsFurtherFromSource);

        $barnaulGaugingStationDistanceFromSource = $barnaulGaugingStation->getDistanceFromSource();

        foreach ($stationsFurtherFromSource as $stationFurtherFromSource) {
            /** @var GaugingStation $stationFurtherFromSource */
            $this->assertTrue($stationFurtherFromSource->getDistanceFromSourceOfAncestorWater($obWater) > $barnaulGaugingStationDistanceFromSource);
        }
    }

    public function testFindAllNotDefinedDistanceFromSource(): void
    {
        $obWater = $this->createWater('Обь');
        $obskoeVodohranWater = $this->createWater('Обское водохранилище');
        $obskoeVodohranWater->setParentWater($obWater);
        $obskoeVodohranWater->setDistanceFromParentWaterSourceInKilometers(150);

        $berdskGaugingStation = $this->createGaugingStationOnDistanceFromSource('Бердск', $obskoeVodohranWater, 50);
        $novosibirskGaugingStation = $this->createGaugingStationOnDistanceFromSource('Новосибирск', $obWater, 210);
        $gaugingStationWithNotDefinedDistance = $this->createGaugingStationOnDistanceFromSource('Сургут', $obWater, null);

        $gaugingStations = new GaugingStationCollection([
            $berdskGaugingStation,
            $novosibirskGaugingStation,
            $gaugingStationWithNotDefinedDistance,
        ]);

        $stationsWithNotDefinedDistanceFromSource = $gaugingStations->findAllNotDefinedDistanceFromSource();

        $this->assertCount(1, $stationsWithNotDefinedDistanceFromSource);

        $this->assertTrue($stationsWithNotDefinedDistanceFromSource->first()->getDistanceFromSource() === null);
    }

    public function testFilterNotContainingDistanceToSource(): void
    {
        $obWater = $this->createWater('Обь');
        $obskoeVodohranWater = $this->createWater('Обское водохранилище');
        $obskoeVodohranWater->setParentWater($obWater);
        $obskoeVodohranWater->setDistanceFromParentWaterSourceInKilometers(150);

        $gaugingStations = new GaugingStationCollection([
            $this->createGaugingStationOnDistanceFromSource('Бердск', $obskoeVodohranWater, 50),
            $this->createGaugingStationOnDistanceFromSource('Новосибирск', $obWater, 210),
            $this->createGaugingStationOnDistanceFromSource('Сургут', $obWater, null),
        ]);

        $this->assertCount(3, $gaugingStations);

        $stationsWithDefinedDistanceFromSource = $gaugingStations->filterNotContainingDistanceToSource();

        $this->assertCount(2, $stationsWithDefinedDistanceFromSource);
    }

    public function testSortByDistanceFromSource(): void
    {
        $obWater = $this->createWater('Обь');
        $obskoeVodohranWater = $this->createWater('Обское водохранилище');
        $obskoeVodohranWater->setParentWater($obWater);
        $obskoeVodohranWater->setDistanceFromParentWaterSourceInKilometers(150);

        $mustBeFirst = $this->createGaugingStationOnDistanceFromSource('Барнаул', $obWater, 100);
        $mustBeSecond = $this->createGaugingStationOnDistanceFromSource('Бердск', $obskoeVodohranWater, 50);
        $mustBeThird = $this->createGaugingStationOnDistanceFromSource('Новосибирск', $obWater, 210);

        $gaugingStations = new GaugingStationCollection([
            $mustBeThird,
            $mustBeFirst,
            $mustBeSecond,
        ]);
        $this->assertFalse($mustBeFirst === $gaugingStations->get(0));

        $sortedStations = $gaugingStations->sortByDistanceFromSource($obWater);

        $this->assertTrue($mustBeFirst === $sortedStations->get(0));
        $this->assertTrue($mustBeSecond === $sortedStations->get(1));
        $this->assertTrue($mustBeThird === $sortedStations->get(2));
    }

    public function testGetGaugingStationDistanceFromSourceNotDefinedExceptionWhenSortByDistance(): void
    {
        $this->expectException(GaugingStationDistanceFromSourceNotDefinedException::class);

        $obWater = $this->createWater('Обь');

        $gaugingStations = new GaugingStationCollection([
            $this->createGaugingStationOnDistanceFromSource('Барнаул', $obWater, 100),
            $this->createGaugingStationOnDistanceFromSource('Сургут', $obWater, null),
        ]);

        $gaugingStations->sortByDistanceFromSource($obWater);
    }

    public function testSortByName(): void
    {
        $obWater = $this->createWater('Обь');

        $mustBeFirst = $this->createGaugingStationOnDistanceFromSource('Барнаул', $obWater, 100);
        $mustBeSecond = $this->createGaugingStationOnDistanceFromSource('Бердск', $obWater, 100);
        $mustBeThird = $this->createGaugingStationOnDistanceFromSource('Новосибирск', $obWater, 100);

        $gaugingStations = new GaugingStationCollection([
            $mustBeThird,
            $mustBeFirst,
            $mustBeSecond,
        ]);
        $this->assertFalse($mustBeFirst === $gaugingStations->get(0));

        $sortedStations = $gaugingStations->sortByName();

        $this->assertTrue($mustBeFirst === $sortedStations->get(0));
        $this->assertTrue($mustBeSecond === $sortedStations->get(1));
        $this->assertTrue($mustBeThird === $sortedStations->get(2));
    }

    public function testSortDeepWaterNestedByDistanceFromSourceAndByNameIfDistanceNotDefined(): void
    {
        $obWater = $this->createWater('Обь');
        $obskoeVodohranWater = $this->createWater('Обское водохранилище');
        $obskoeVodohranWater->setParentWater($obWater);
        $obskoeVodohranWater->setDistanceFromParentWaterSourceInKilometers(150);

        $obskoeVodohranWaterBay1 = $this->createWater('Обское водохранилище - отрезок №1');
        $obskoeVodohranWaterBay1->setParentWater($obskoeVodohranWater);
        $obskoeVodohranWaterBay1->setDistanceFromParentWaterSourceInKilometers(50);
        $obskoeVodohranWaterBay2 = $this->createWater('Обское водохранилище - отрезок №2');
        $obskoeVodohranWaterBay2->setParentWater($obskoeVodohranWater);
        $obskoeVodohranWaterBay2->setDistanceFromParentWaterSourceInKilometers(100);

        $tomWater = $this->createWater('Томь');
        $tomWater->setParentWater($obWater);
        $tomWater->setDistanceFromParentWaterSourceInKilometers(500);

        $tomWaterBay1 = $this->createWater('Томь - отрезок №1');
        $tomWaterBay1->setParentWater($tomWater);
        $tomWaterBay1->setDistanceFromParentWaterSourceInKilometers(100);
        $tomWaterBay2 = $this->createWater('Томь - отрезок №2');
        $tomWaterBay2->setParentWater($tomWater);
        $tomWaterBay2->setDistanceFromParentWaterSourceInKilometers(200);

        $mustBe0 = $this->createGaugingStationOnDistanceFromSource('Барнаул', $obWater, 100);
        $mustBe1 = $this->createGaugingStationOnDistanceFromSource('Спирино', $obskoeVodohranWaterBay1, 10);
        $mustBe2 = $this->createGaugingStationOnDistanceFromSource('Кирза', $obskoeVodohranWaterBay1, 30);
        $mustBe3 = $this->createGaugingStationOnDistanceFromSource('Ордынское', $obskoeVodohranWaterBay2, 10);
        $mustBe4 = $this->createGaugingStationOnDistanceFromSource('Боровое', $obskoeVodohranWaterBay2, 30);
        $mustBe5 = $this->createGaugingStationOnDistanceFromSource('Бердск', $obskoeVodohranWater, 150);

        $mustBe6 = $this->createGaugingStationOnDistanceFromSource('Новосибирск', $obWater, 310);
        $mustBe7 = $this->createGaugingStationOnDistanceFromSource('Кемерово', $tomWater, 50);
        $mustBe8 = $this->createGaugingStationOnDistanceFromSource('Новороманово', $tomWaterBay1, 50);
        $mustBe9 = $this->createGaugingStationOnDistanceFromSource('Юрга', $tomWaterBay1, 80);
        $mustBe10 = $this->createGaugingStationOnDistanceFromSource('Кулаково', $tomWaterBay2, 20);
        $mustBe11 = $this->createGaugingStationOnDistanceFromSource('Вершинино', $tomWaterBay2, 60);

        $mustBe12 = $this->createGaugingStationOnDistanceFromSource('Береговое', $obWater, null);
        $mustBe13 = $this->createGaugingStationOnDistanceFromSource('Березово', $obWater, null);
        $mustBe14 = $this->createGaugingStationOnDistanceFromSource('Сургут', $obWater, null);

        $gaugingStations = new GaugingStationCollection([
            $mustBe2, $mustBe5, $mustBe8, $mustBe11, $mustBe14,
            $mustBe1, $mustBe4, $mustBe7, $mustBe10, $mustBe13,
            $mustBe0, $mustBe3, $mustBe6, $mustBe9, $mustBe12,
        ]);

        $sortedStations = $gaugingStations->sortByDistanceFromSourceAndByNameIfDistanceNotDefined($obWater);

        $this->assertTrue($mustBe0 === $sortedStations->get(0));
        $this->assertTrue($mustBe1 === $sortedStations->get(1));
        $this->assertTrue($mustBe2 === $sortedStations->get(2));
        $this->assertTrue($mustBe3 === $sortedStations->get(3));
        $this->assertTrue($mustBe4 === $sortedStations->get(4));
        $this->assertTrue($mustBe5 === $sortedStations->get(5));
        $this->assertTrue($mustBe6 === $sortedStations->get(6));
        $this->assertTrue($mustBe7 === $sortedStations->get(7));
        $this->assertTrue($mustBe8 === $sortedStations->get(8));
        $this->assertTrue($mustBe9 === $sortedStations->get(9));
        $this->assertTrue($mustBe10 === $sortedStations->get(10));
        $this->assertTrue($mustBe11 === $sortedStations->get(11));
        $this->assertTrue($mustBe12 === $sortedStations->get(12));
        $this->assertTrue($mustBe13 === $sortedStations->get(13));
        $this->assertTrue($mustBe14 === $sortedStations->get(14));
    }

    public function testGetLast(): void
    {
        $obWater = $this->createWater('Обь');

        $mustBeFirst = $this->createGaugingStationOnDistanceFromSource('Барнаул', $obWater, 100);
        $mustBeSecond = $this->createGaugingStationOnDistanceFromSource('Бердск', $obWater, 100);
        $notIncluded = $this->createGaugingStationOnDistanceFromSource('Новосибирск', $obWater, 100);

        $gaugingStations = new GaugingStationCollection([
            $notIncluded,
            $mustBeFirst,
            $mustBeSecond,
        ]);

        $pickedStations = $gaugingStations->getLast(2);

        $this->assertNotContains($notIncluded, $pickedStations);
        $this->assertContains($mustBeFirst, $gaugingStations);
        $this->assertContains($mustBeSecond, $gaugingStations);
    }

    public function testGetFirst(): void
    {
        $obWater = $this->createWater('Обь');

        $notIncluded = $this->createGaugingStationOnDistanceFromSource('Барнаул', $obWater, 100);
        $mustBeFirst = $this->createGaugingStationOnDistanceFromSource('Бердск', $obWater, 100);
        $mustBeSecond = $this->createGaugingStationOnDistanceFromSource('Новосибирск', $obWater, 100);

        $gaugingStations = new GaugingStationCollection([
            $mustBeFirst,
            $mustBeSecond,
            $notIncluded,
        ]);

        $pickedStations = $gaugingStations->getFirst(2);

        $this->assertNotContains($notIncluded, $pickedStations);
        $this->assertContains($mustBeFirst, $gaugingStations);
        $this->assertContains($mustBeSecond, $gaugingStations);
    }

    public function testSortByDistanceToCoordinate(): void
    {
        $obWater = $this->createWater('Обь');

        $mustBeFirst = $this->createGaugingStationOnDistanceFromSource(
            'Барнаул',
            $obWater,
            100,
            new Coordinates(1.0, 1.1)
        );

        $mustBeSecond = $this->createGaugingStationOnDistanceFromSource(
            'Бердск',
            $obWater,
            100,
            new Coordinates(5.0, 3.1)
        );

        $mustBeThird = $this->createGaugingStationOnDistanceFromSource(
            'Новосибирск',
            $obWater,
            100,
            new Coordinates(-10.0, -11.1)
        );

        $gaugingStations = new GaugingStationCollection([
            $mustBeThird,
            $mustBeFirst,
            $mustBeSecond,
        ]);
        $this->assertFalse($mustBeFirst === $gaugingStations->get(0));

        $sortedStations = $gaugingStations->sortByDistanceToCoordinates(new Coordinates(0.0, 0.0));

        $this->assertTrue($mustBeFirst === $sortedStations->get(0));
        $this->assertTrue($mustBeSecond === $sortedStations->get(1));
        $this->assertTrue($mustBeThird === $sortedStations->get(2));
    }

    private function createGaugingStationOnDistanceFromSource(string $name, Water $water, ?float $distanceFromSource, ?Coordinates $coordinates = null): GaugingStation
    {
        if ($coordinates === null) {
            $coordinates = new Coordinates(
                rand(-90, 90),
                rand(-180, 180)
            );
        }

        return new GaugingStation(
            Uuid::uuid4(),
            'short-uuid',
            '',
            $water,
            $name,
            new GeographicalPosition($coordinates, $distanceFromSource, null, null),
        );
    }

    private function createWater(string $waterName): Water
    {
        return new Water(
            Uuid::uuid4(),
            '',
            $waterName,
            WaterType::river()
        );
    }
}

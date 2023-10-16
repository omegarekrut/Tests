<?php

namespace Tests\Unit\Util\Coordinates;

use App\Util\Coordinates\Coordinates;
use Tests\Unit\TestCase;

class CoordinatesTest extends TestCase
{
    /**
     * @dataProvider dataGetDistance
     */
    public function testGetDistance(Coordinates $coordinatesFrom, Coordinates $coordinatesTo, float $expectedDistance): void
    {
        $distance = $coordinatesFrom->getDistanceToCoordinateInKilometres($coordinatesTo);

        $this->assertEquals($expectedDistance, round($distance, 3));
    }

    /**
     * @return mixed[]
     */
    public function dataGetDistance(): array
    {
        return [
            'distanceWhenSameCoordinates' => [
                new Coordinates(55.751244, 37.618423),
                new Coordinates(55.751244, 37.618423),
                .0,
            ],
            'distanceBetweenNovosibirskAndMoscow' => [
                new Coordinates(55.7522200, 37.6155600),
                new Coordinates(55.0415000, 82.9346000),
                2811.428,
            ],
            'distanceBetweenNovosibirskAndBrazil' => [
                new Coordinates(55.7522200, 37.6155600),
                new Coordinates(-15.7982615, -47.8754724),
                11176.655,
            ],
        ];
    }
}

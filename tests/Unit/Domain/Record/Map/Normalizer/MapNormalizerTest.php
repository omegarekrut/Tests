<?php

namespace Tests\Unit\Domain\Record\Map\Normalizer;

use App\Domain\Record\Map\Entity\Map;
use App\Domain\Record\Map\Normalizer\MapNormalizer;
use App\Util\Coordinates\Coordinates;
use Tests\Unit\TestCase;

/**
 * @group map
 */
class MapNormalizerTest extends TestCase
{
    /** @var MapNormalizer */
    private $mapNormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapNormalizer = new MapNormalizer();
    }

    public function testSerializeMapCollection(): void
    {
        $map = $this->createMap(1, new Coordinates('10', '20'));

        $this->assertTrue($this->mapNormalizer->supportsNormalization($map));

        $normalizedMap = $this->mapNormalizer->normalize($map);

        $this->assertEquals([
            'id' => 1,
            'latitude' => 10,
            'longitude' => 20,
        ], $normalizedMap);
    }

    public function testUnsupportedType(): void
    {
        $this->assertFalse($this->mapNormalizer->supportsNormalization($this));
    }

    private function createMap(int $id, Coordinates $coordinates): Map
    {
        $map = $this->createMock(Map::class);
        $map
            ->method('getId')
            ->willReturn($id);
        $map
            ->method('getCoordinates')
            ->willReturn($coordinates);

        return $map;
    }
}

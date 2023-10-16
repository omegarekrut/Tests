<?php

namespace Tests\Unit\Domain\Region\Normalizer;

use App\Domain\Region\Entity\Country;
use App\Domain\Region\Entity\Region;
use App\Domain\Region\Normalizer\RegionNormalizer;
use Ramsey\Uuid\Uuid;
use Tests\Traits\FakerFactoryTrait;
use Tests\Unit\TestCase;

class RegionNormalizerTest extends TestCase
{
    use FakerFactoryTrait;

    private RegionNormalizer $regionNormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regionNormalizer = new RegionNormalizer();
    }

    protected function tearDown(): void
    {
        unset($this->regionNormalizer);

        parent::tearDown();
    }

    public function testNormalizeForListOfRegionsWithVisitorRegion(): void
    {
        $simpleArea = $this->getMockRegion('обл');
        $federalCity = $this->getMockRegion('г');
        $republicRegion = $this->getMockRegion('Респ');
        $visitorRegion = $this->getMockRegion('обл', [(string) $republicRegion->getId()]);

        $regions = [
            $simpleArea,
            $federalCity,
            $visitorRegion,
            $republicRegion,
        ];

        $expectedNormalizedData = [
            [
                'id' => (string) $simpleArea->getId(),
                'name' => sprintf('%s %s', $simpleArea->getShortName(), $simpleArea->getRegionType()),
                'country' => $simpleArea->getCountry()->getName(),
                'isPreferred' => false,
            ],
            [
                'id' => (string) $federalCity->getId(),
                'name' => $federalCity->getShortName(),
                'country' => $federalCity->getCountry()->getName(),
                'isPreferred' => false,
            ],
            [
                'id' => (string) $visitorRegion->getId(),
                'name' => sprintf('%s %s', $visitorRegion->getShortName(), $visitorRegion->getRegionType()),
                'country' => $visitorRegion->getCountry()->getName(),
                'isPreferred' => true,
            ],
            [
                'id' => (string) $republicRegion->getId(),
                'name' => $republicRegion->getShortName(),
                'country' => $republicRegion->getCountry()->getName(),
                'isPreferred' => true,
            ],
        ];

        $this->assertEquals($expectedNormalizedData, $this->regionNormalizer->normalizeForModalSelectList($regions, $visitorRegion));
    }

    public function testNormalizeForListOfRegionsWithManySurroundsRegionsInVisitorRegion(): void
    {
        $simpleArea = $this->getMockRegion('обл');
        $federalCity = $this->getMockRegion('г');
        $republicRegion = $this->getMockRegion('Респ');
        $firstSurroundsimpleAreaRegion = $this->getMockRegion('обл');
        $secondSurroundsimpleAreaRegion = $this->getMockRegion('обл');
        $thirdSurroundsimpleAreaRegion = $this->getMockRegion('обл');
        $fourthSurroundsimpleAreaRegion = $this->getMockRegion('обл');
        $fifthSurroundsimpleAreaRegion = $this->getMockRegion('обл');
        $regionWithManySurroundRegion = $this->getMockRegion(
            'обл',
            [
                (string) $firstSurroundsimpleAreaRegion->getId(),
                (string) $secondSurroundsimpleAreaRegion->getId(),
                (string) $thirdSurroundsimpleAreaRegion->getId(),
                (string) $fourthSurroundsimpleAreaRegion->getId(),
                (string) $fifthSurroundsimpleAreaRegion->getId(),
            ]
        );

        $regions = [
            $simpleArea,
            $federalCity,
            $republicRegion,
            $regionWithManySurroundRegion,
            $firstSurroundsimpleAreaRegion,
            $secondSurroundsimpleAreaRegion,
            $thirdSurroundsimpleAreaRegion,
            $fourthSurroundsimpleAreaRegion,
            $fifthSurroundsimpleAreaRegion,
        ];

        $expectedNormalizedData = [
            [
                'id' => (string) $simpleArea->getId(),
                'name' => sprintf('%s %s', $simpleArea->getShortName(), $simpleArea->getRegionType()),
                'country' => $simpleArea->getCountry()->getName(),
                'isPreferred' => false,
            ],
            [
                'id' => (string) $federalCity->getId(),
                'name' => $federalCity->getShortName(),
                'country' => $federalCity->getCountry()->getName(),
                'isPreferred' => false,
            ],
            [
                'id' => (string) $republicRegion->getId(),
                'name' => $republicRegion->getShortName(),
                'country' => $republicRegion->getCountry()->getName(),
                'isPreferred' => false,
            ],
            [
                'id' => (string) $regionWithManySurroundRegion->getId(),
                'name' => sprintf('%s %s', $regionWithManySurroundRegion->getShortName(), $regionWithManySurroundRegion->getRegionType()),
                'country' => $regionWithManySurroundRegion->getCountry()->getName(),
                'isPreferred' => true,
            ],
            [
                'id' => (string) $firstSurroundsimpleAreaRegion->getId(),
                'name' => sprintf('%s %s', $firstSurroundsimpleAreaRegion->getShortName(), $firstSurroundsimpleAreaRegion->getRegionType()),
                'country' => $firstSurroundsimpleAreaRegion->getCountry()->getName(),
                'isPreferred' => true,
            ],
            [
                'id' => (string) $secondSurroundsimpleAreaRegion->getId(),
                'name' => sprintf('%s %s', $secondSurroundsimpleAreaRegion->getShortName(), $secondSurroundsimpleAreaRegion->getRegionType()),
                'country' => $secondSurroundsimpleAreaRegion->getCountry()->getName(),
                'isPreferred' => true,
            ],
            [
                'id' => (string) $thirdSurroundsimpleAreaRegion->getId(),
                'name' => sprintf('%s %s', $thirdSurroundsimpleAreaRegion->getShortName(), $thirdSurroundsimpleAreaRegion->getRegionType()),
                'country' => $thirdSurroundsimpleAreaRegion->getCountry()->getName(),
                'isPreferred' => true,
            ],
            [
                'id' => (string) $fourthSurroundsimpleAreaRegion->getId(),
                'name' => sprintf('%s %s', $fourthSurroundsimpleAreaRegion->getShortName(), $fourthSurroundsimpleAreaRegion->getRegionType()),
                'country' => $fourthSurroundsimpleAreaRegion->getCountry()->getName(),
                'isPreferred' => true,
            ],
            [
                'id' => (string) $fifthSurroundsimpleAreaRegion->getId(),
                'name' => sprintf('%s %s', $fifthSurroundsimpleAreaRegion->getShortName(), $fifthSurroundsimpleAreaRegion->getRegionType()),
                'country' => $fifthSurroundsimpleAreaRegion->getCountry()->getName(),
                'isPreferred' => false,
            ],
        ];

        $this->assertEquals($expectedNormalizedData, $this->regionNormalizer->normalizeForModalSelectList($regions, $regionWithManySurroundRegion));
    }

    public function testNormalizeForListOfRegionsWithoutVisitorRegion(): void
    {
        $simpleArea = $this->getMockRegion('обл');
        $federalCity = $this->getMockRegion('г');
        $republicRegion = $this->getMockRegion('Респ');
        $regionWithSurroundRegions = $this->getMockRegion('обл', [(string) $republicRegion->getId()]);

        $regions = [
            $simpleArea,
            $federalCity,
            $regionWithSurroundRegions,
            $republicRegion,
        ];

        $expectedNormalizedData = [
            [
                'id' => (string) $simpleArea->getId(),
                'name' => sprintf('%s %s', $simpleArea->getShortName(), $simpleArea->getRegionType()),
                'country' => $simpleArea->getCountry()->getName(),
                'isPreferred' => false,
            ],
            [
                'id' => (string) $federalCity->getId(),
                'name' => $federalCity->getShortName(),
                'country' => $federalCity->getCountry()->getName(),
                'isPreferred' => true,
            ],
            [
                'id' => (string) $regionWithSurroundRegions->getId(),
                'name' => sprintf('%s %s', $regionWithSurroundRegions->getShortName(), $regionWithSurroundRegions->getRegionType()),
                'country' => $regionWithSurroundRegions->getCountry()->getName(),
                'isPreferred' => false,
            ],
            [
                'id' => (string) $republicRegion->getId(),
                'name' => $republicRegion->getShortName(),
                'country' => $republicRegion->getCountry()->getName(),
                'isPreferred' => false,
            ],
        ];

        $this->assertEquals($expectedNormalizedData, $this->regionNormalizer->normalizeForModalSelectList($regions, null));
    }

    public function testNormalizeForListOfRegionsWithEmptyRegions(): void
    {
        $this->assertEquals([], $this->regionNormalizer->normalizeForModalSelectList([], null));
    }

    /**
     * @param string[] $surroundRegionIds
     */
    private function getMockRegion(string $regionType, array $surroundRegionIds = []): Region
    {
        $region = $this->createMock(Region::class);
        $region
            ->method('getId')
            ->willReturn(Uuid::uuid4());

        $region
            ->method('getShortName')
            ->willReturn($this->getFaker()->region);

        $region
            ->method('getRegionType')
            ->willReturn($regionType);

        $region
            ->method('getCountry')
            ->willReturn($this->getMockCountry());

        $region
            ->method('getSurroundRegionIds')
            ->willReturn($surroundRegionIds);

        return $region;
    }

    private function getMockCountry(): Country
    {
        $country = $this->createMock(Country::class);
        $country->method('getName')->willReturn($this->getFaker()->country);

        return $country;
    }
}

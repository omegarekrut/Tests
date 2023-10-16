<?php

namespace Tests\Unit\Twig\WaterLevel;

use App\Domain\WaterLevel\Entity\ValueObject\GaugingStationRecordsProviderKey;
use App\Twig\WaterLevel\GaugingStationRecordsProviderTitle;
use Generator;
use Tests\Unit\TestCase;

/**
 * @group twig
 * @group gauging-station-records-provider
 */
class GaugingStationRecordsProviderTitleTest extends TestCase
{
    /**
     * @dataProvider gaugingStationRecordsProvider
     */
    public function testFilter(GaugingStationRecordsProviderKey $providerKey, string $expectedProviderKey): void
    {
        $filter = new GaugingStationRecordsProviderTitle();

        $this->assertEquals($expectedProviderKey, $filter($providerKey));
    }

    public function gaugingStationRecordsProvider(): Generator
    {
        yield [
            GaugingStationRecordsProviderKey::meteoNso(),
            'ФГБУ "Западно-Сибирского УГМС"',
        ];

        yield [
            GaugingStationRecordsProviderKey::esimo(),
            'МФИС "Единая государственная система информации об обстановке в Мировом океане"',
        ];
    }

    /**
     * @dataProvider gaugingStationRecordsProviderKeys
     */
    public function testFilterValuesForAllProviderKeysExist(GaugingStationRecordsProviderKey $providerKey): void
    {
        $filter = new GaugingStationRecordsProviderTitle();

        $this->assertNotEmpty($filter($providerKey));
    }
    
    /**
     * GaugingStationRecordsProviderKey[]
     */
    public function gaugingStationRecordsProviderKeys(): Generator
    {
        foreach (GaugingStationRecordsProviderKey::values() as $providerKey) {
            yield [$providerKey];
        }
    }
}

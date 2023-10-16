<?php

namespace Tests\Unit\Twig\WaterLevel;

use App\Domain\WaterLevel\Entity\ValueObject\GaugingStationRecordsProviderKey;
use App\Twig\WaterLevel\GaugingStationRecordsProviderLink;
use Generator;
use Tests\Unit\TestCase;

/**
 * @group twig
 * @group gauging-station-records-provider
 */
class GaugingStationRecordsProviderLinkTest extends TestCase
{
    /**
     * @dataProvider gaugingStationRecordsProvider
     */
    public function testFilter(GaugingStationRecordsProviderKey $providerKey, string $expectedProviderKey): void
    {
        $filter = new GaugingStationRecordsProviderLink();

        $this->assertEquals($expectedProviderKey, $filter($providerKey));
    }

    public function gaugingStationRecordsProvider(): Generator
    {
        yield [
            GaugingStationRecordsProviderKey::meteoNso(),
            'http://meteo-nso.ru',
        ];

        yield [
            GaugingStationRecordsProviderKey::esimo(),
            'http://esimo.ru',
        ];
    }

    /**
     * @dataProvider gaugingStationRecordsProviderKeys
     */
    public function testFilterValuesForAllProviderKeysExist(GaugingStationRecordsProviderKey $providerKey): void
    {
        $filter = new GaugingStationRecordsProviderLink();

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

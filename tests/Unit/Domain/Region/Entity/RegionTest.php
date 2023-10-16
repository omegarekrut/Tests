<?php

namespace Tests\Unit\Domain\Region\Entity;

use App\Domain\Region\Entity\Country;
use App\Domain\Region\Entity\Region;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;
use DateTimeZone;

class RegionTest extends TestCase
{
    public function testGetDateTimeZoneForRegionWithoutTimezone(): void
    {
        $region = $this->getRegionEntityWithTimezone(null);

        $this->assertNull($region->getDateTimeZone());
    }

    public function testGetDateTimeZoneForRegionWithTimezoneOffsetLessThan10(): void
    {
        $region = $this->getRegionEntityWithTimezone('UTC+3');

        $this->assertEquals(new DateTimeZone('+0300'), $region->getDateTimeZone());
    }

    public function testGetDateTimeZoneForRegionWithTimezoneOffsetMoreThan10(): void
    {
        $region = $this->getRegionEntityWithTimezone('UTC+11');

        $this->assertEquals(new DateTimeZone('+1100'), $region->getDateTimeZone());
    }

    public function testGetDateTimeZoneForRegionWithNegativeTimezoneOffsetLessThan10(): void
    {
        $region = $this->getRegionEntityWithTimezone('UTC-3');

        $this->assertEquals(new DateTimeZone('-0300'), $region->getDateTimeZone());
    }

    public function testGetDateTimeZoneForRegionWithNegativeTimezoneOffsetMoreThan10(): void
    {
        $region = $this->getRegionEntityWithTimezone('UTC-10');

        $this->assertEquals(new DateTimeZone('-1000'), $region->getDateTimeZone());
    }

    private function getRegionEntityWithTimezone(?string $timezone): Region
    {
        $country = $this->createMock(Country::class);

        return new Region(
            Uuid::uuid4(),
            $country,
            '42',
            'Test name',
            'short name',
            $timezone
        );
    }
}

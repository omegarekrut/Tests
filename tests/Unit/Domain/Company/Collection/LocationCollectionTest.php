<?php

namespace Tests\Unit\Domain\Company\Collection;

use App\Domain\Company\Collection\LocationCollection;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Location;
use App\Domain\Region\Entity\Region;
use InvalidArgumentException;
use Tests\Unit\TestCase;

class LocationCollectionTest extends TestCase
{
    public function testLocationCollectionCantBeCreatedWithNotLocationElement(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('LocationCollection element must be instance of %s', Location::class));

        $notLocationArray = [$this->createMock(Company::class)];
        new LocationCollection($notLocationArray);
    }

    public function testLocationCollectionCanBeCreatedWithLocationElement(): void
    {
        $location = $this->createMock(Location::class);

        $locationCollection = new LocationCollection([$location]);

        $this->assertContains($location, $locationCollection);
    }

    public function testCanAddLocationToLocationCollection(): void
    {
        $location = $this->createMock(Location::class);

        $locationCollection = new LocationCollection();
        $locationCollection->add($location);

        $this->assertContains($location, $locationCollection);
    }

    public function testCantAddNotLocationToLocationCollection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('LocationCollection element must be instance of %s', Location::class));

        $notLocation = $this->createMock(Company::class);

        $locationCollection = new LocationCollection();
        $locationCollection->add($notLocation);
    }

    public function testCanSetLocationToLocationCollection(): void
    {
        $location = $this->createMock(Location::class);

        $locationCollection = new LocationCollection();
        $locationCollection->set(1, $location);

        $this->assertContains($location, $locationCollection);
    }

    public function testFilterByRegion(): void
    {
        $currentRegion = $this->createMock(Region::class);

        $locations = [];

        for ($i = 0; $i <= 5; $i++) {
            $location = $this->createMock(Location::class);
            $location->method('getRegion')->willReturn($this->createMock(Region::class));
            $locations[] = $location;
        }

        $location = $this->createMock(Location::class);
        $location->method('getRegion')->willReturn($currentRegion);
        $locations[] = $location;

        $locationCollection = new LocationCollection($locations);
        $locationCollection = $locationCollection->filterByRegion($currentRegion);

        $this->assertEquals(1, $locationCollection->count());
        $this->assertContains($location, $locationCollection);
    }

    public function testCantSetNotLocationToLocationCollection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('LocationCollection element must be instance of %s', Location::class));

        $notLocation = $this->createMock(Company::class);

        $locationCollection = new LocationCollection();
        $locationCollection->set(1, $notLocation);
    }
}

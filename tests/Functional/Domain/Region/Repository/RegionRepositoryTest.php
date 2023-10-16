<?php

namespace Tests\Functional\Domain\Region\Repository;

use App\Domain\Region\Repository\RegionRepository;
use Tests\DataFixtures\ORM\Region\Region\LoadIrkutskRegion;
use Tests\DataFixtures\ORM\Region\Region\LoadNovosibirskRegion;
use Tests\DataFixtures\ORM\Region\Region\LoadRegionFromNotShowedCountry;
use Tests\Functional\TestCase;

class RegionRepositoryTest extends TestCase
{
    protected $regionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            LoadIrkutskRegion::class,
            LoadNovosibirskRegion::class,
            LoadRegionFromNotShowedCountry::class,
        ]);

        $this->regionRepository = $this->getContainer()->get(RegionRepository::class);
    }

    protected function tearDown(): void
    {
        unset($this->regionRepository);

        parent::tearDown();
    }

    public function testGetAllForShowedCountries(): void
    {
        $this->assertCount(2, $this->regionRepository->getAllForShowedCountries());
    }

    public function testFindOneByName(): void
    {
        $this->assertNotNull($this->regionRepository->findOneByName('Новосибирская область'));
    }

    public function testFindOneByFiasId(): void
    {
        $region = $this->regionRepository->findOneByMappingId(LoadNovosibirskRegion::FAKE_NOVOSIBIRSK_FIAS_ID);
        $this->assertEquals('Новосибирская область', $region->getName());
    }
}

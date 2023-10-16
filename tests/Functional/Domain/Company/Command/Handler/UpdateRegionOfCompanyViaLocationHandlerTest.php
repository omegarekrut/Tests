<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Company\Command\UpdateRegionOfCompanyViaLocationCommand;
use App\Domain\Company\Entity\Company;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithFixedCoordinates;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\Functional\TestCase;

class UpdateRegionOfCompanyViaLocationHandlerTest extends TestCase
{
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithFixedCoordinates::class,
            LoadTestRegion::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadCompanyWithFixedCoordinates::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->company);

        parent::tearDown();
    }

    /**
     * TODO https://resolventa.atlassian.net/browse/FS-3079
     * Временное решение, настроить для работы со всеми адресами из коллекции
     */
    public function testHandle(): void
    {
        $command = new UpdateRegionOfCompanyViaLocationCommand($this->company->getId());

        $this->assertNull($this->company->getContact()->getLocations()->first()->getRegion());

        $this->getCommandBus()->handle($command);

        $this->assertEquals(LoadTestRegion::REGION_NAME, $this->company->getContact()->getLocations()->first()->getRegion()->getName());
    }
}

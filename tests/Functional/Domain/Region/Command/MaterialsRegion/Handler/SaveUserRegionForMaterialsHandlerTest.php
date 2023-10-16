<?php

namespace Tests\Functional\Domain\Region\Command\MaterialsRegion\Handler;

use App\Auth\Visitor\MaterialsRegion\MaterialsRegionInCookieStorage;
use App\Domain\Region\Command\MaterialsRegion\SaveUserRegionForMaterialsCommand;
use App\Domain\Region\Entity\Region;
use Tests\DataFixtures\ORM\Region\Region\LoadNovosibirskRegion;
use Tests\Functional\TestCase;

class SaveUserRegionForMaterialsHandlerTest extends TestCase
{
    public function testHandleWithRegion(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNovosibirskRegion::class,
        ])->getReferenceRepository();

        $expectedRegion = $referenceRepository->getReference(LoadNovosibirskRegion::REFERENCE_NAME);

        $command = new SaveUserRegionForMaterialsCommand();
        $command->regionId = $expectedRegion->getId();

        $this->getCommandBus()->handle($command);

        $this->assertSame($expectedRegion, $this->getRegionFromCookie());
    }

    private function getRegionFromCookie(): ?Region
    {
        $materialsRegionInCookieStorage = $this->getContainer()->get(MaterialsRegionInCookieStorage::class);

        $cookie = $materialsRegionInCookieStorage->getCookie();

        return $materialsRegionInCookieStorage->parseCookie($cookie->getValue());
    }
}

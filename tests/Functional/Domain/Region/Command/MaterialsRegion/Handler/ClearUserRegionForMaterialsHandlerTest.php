<?php

namespace Tests\Functional\Domain\Region\Command\MaterialsRegion\Handler;

use App\Auth\Visitor\MaterialsRegion\MaterialsRegionInCookieStorage;
use App\Domain\Region\Command\MaterialsRegion\ClearUserRegionForMaterialsCommand;
use Tests\Functional\TestCase;

class ClearUserRegionForMaterialsHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $command = new ClearUserRegionForMaterialsCommand();
        $expectedCookieValue = $command->valueForClearCookie;

        $this->getCommandBus()->handle($command);

        $materialsRegionInCookieStorage = $this->getContainer()->get(MaterialsRegionInCookieStorage::class);
        $cookie = $materialsRegionInCookieStorage->getCookie();

        $this->assertSame($expectedCookieValue, $cookie->getValue());
    }
}

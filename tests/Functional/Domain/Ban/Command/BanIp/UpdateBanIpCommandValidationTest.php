<?php

namespace Tests\Functional\Domain\Ban\Command\BanIp;

use App\Domain\Ban\Command\BanIp\UpdateBanIpCommand;
use Tests\DataFixtures\ORM\Ban\LoadBanIp;
use Tests\Functional\ValidationTestCase;

/**
 * @group ban
 */
class UpdateBanIpCommandValidationTest extends ValidationTestCase
{
    public function testToDoNothing(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadBanIp::class,
        ])->getReferenceRepository();

        $command = new UpdateBanIpCommand($referenceRepository->getReference(LoadBanIp::BAN_IP));
        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}

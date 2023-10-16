<?php

namespace Tests\Functional\Domain\Ban\Command\BanUser;

use App\Domain\Ban\Command\BanUser\UpdateBanUserCommand;
use Tests\DataFixtures\ORM\Ban\LoadBanUsers;
use Tests\Functional\ValidationTestCase;

/**
 * @group ban
 */
class UpdateBanUserCommandValidationTest extends ValidationTestCase
{
    public function testToDoNothing(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadBanUsers::class,
        ])->getReferenceRepository();

        $command = new UpdateBanUserCommand($referenceRepository->getReference(LoadBanUsers::BAN_USER));

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}

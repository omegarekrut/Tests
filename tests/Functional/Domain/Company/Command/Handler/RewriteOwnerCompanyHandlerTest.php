<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Company\Command\RewriteOwnerCompanyCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadTackleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;
use Tests\Functional\TestCase;

/**
 * @group company
 */
class RewriteOwnerCompanyHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNumberedUsers::class,
            LoadTackleShopsCompany::class,
        ])->getReferenceRepository();

        /** @var User $owner */
        $owner = $referenceRepository->getReference(LoadNumberedUsers::getRandReferenceName());

        /** @var Company $company */
        $company = $referenceRepository->getReference(LoadTackleShopsCompany::REFERENCE_NAME);

        $command = new RewriteOwnerCompanyCommand($company);
        $command->owner = $owner;

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->owner, $company->getOwner());
    }
}

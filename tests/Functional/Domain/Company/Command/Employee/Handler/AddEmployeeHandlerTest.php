<?php

namespace Tests\Functional\Domain\Company\Command\Employee\Handler;

use App\Domain\Company\Command\Employee\AddEmployeeCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

/**
 * @group company
 */
final class AddEmployeeHandlerTest extends TestCase
{
    private ReferenceRepository $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        unset($this->referenceRepository);

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $user = $this->referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($user instanceof User);
        $company = $this->referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $command = new AddEmployeeCommand();
        $command->companyId = $company->getId();
        $command->userLoginOrEmail = $user->getEmailAddress();

        $this->getCommandBus()->handle($command);

        $actualEmployee = $company->getEmployees()->first();

        $this->assertNotNull($actualEmployee);
        $this->assertSame($user, $actualEmployee->getUser());
        $this->assertSame($company, $actualEmployee->getCompany());
    }
}

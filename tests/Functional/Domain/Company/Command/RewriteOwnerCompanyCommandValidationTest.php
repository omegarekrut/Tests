<?php

namespace Tests\Functional\Domain\Company\Command;

use App\Domain\Company\Command\RewriteOwnerCompanyCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Ban\LoadBanUsers;
use Tests\DataFixtures\ORM\Company\Company\LoadTackleShopsGenerateCompany;
use Tests\DataFixtures\ORM\User\LoadBannedUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;
use Tests\Functional\ValidationTestCase;

/**
 * @group company
 */
class RewriteOwnerCompanyCommandValidationTest extends ValidationTestCase
{
    private RewriteOwnerCompanyCommand $command;
    private User $bannedUser;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTackleShopsGenerateCompany::class,
            LoadNumberedUsers::class,
            LoadBannedUser::class,
            LoadBanUsers::class,
        ])->getReferenceRepository();

        /** @var Company $company */
        $company = $referenceRepository->getReference(LoadTackleShopsGenerateCompany::getRandReferenceName());

        $this->owner = $referenceRepository->getReference(LoadNumberedUsers::getRandReferenceName());

        $this->bannedUser = $referenceRepository->getReference(LoadBannedUser::USER_BANNED);

        $this->command = new RewriteOwnerCompanyCommand($company);
    }

    protected function tearDown(): void
    {
        unset(
            $this->command,
            $this->bannedUser,
        );

        parent::tearDown();
    }

    public function testNotBlankOwnerField(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('owner', 'Это поле обязательно для заполнения.');
    }

    public function testBlockedUserCannotBeOwner(): void
    {
        $this->command->owner = $this->bannedUser;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('owner', 'Заблокированный пользователь не может быть владельцем компании.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->owner = $this->owner;

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}

<?php

namespace Tests\Functional\Domain\Company\Command\OwnershipRequest;

use App\Domain\Company\Command\OwnershipRequest\CreateOwnershipRequestCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest;
use Tests\DataFixtures\ORM\Company\OwnershipRequest\LoadFakeOwnershipRequestToFutureApprove;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\ValidationTestCase;

/**
 * @group company
 */
class CreateOwnershipRequestCommandValidationTest extends ValidationTestCase
{
    private CreateOwnershipRequestCommand $command;
    private User $existsCreator;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadFakeOwnershipRequestToFutureApprove::class,
            LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest::class,
            LoadUserWithAvatar::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var Company $company */
        $company = $referenceRepository->getReference(LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest::REFERENCE_NAME);

        /** @var User $creator */
        $creator = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);

        $this->existsCreator = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $this->command = new CreateOwnershipRequestCommand($creator, $company);
    }
    protected function tearDown(): void
    {
        unset(
            $this->command,
            $this->existsCreator,
        );

        parent::tearDown();
    }

    public function testCaseOwnershipRequestAlreadyExists(): void
    {
        $this->command->creator = $this->existsCreator;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('creator', 'Вы уже отправляли запрос на подтверждения статуса владельца. Ожидайте ответа модератора.');
    }

    public function testWithValidData(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}

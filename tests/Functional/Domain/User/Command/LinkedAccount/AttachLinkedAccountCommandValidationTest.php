<?php

namespace Tests\Functional\Domain\User\Command\LinkedAccount;

use App\Domain\User\Command\LinkedAccount\AttachLinkedAccountCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadUsersWithLinkedAccount;
use Tests\Functional\ValidationTestCase;

/**
 * @group auth
 */
class AttachLinkedAccountCommandValidationTest extends ValidationTestCase
{
    private const INVALID_USER_ID = -1;

    /** @var AttachLinkedAccountCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new AttachLinkedAccountCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testUserNotExists(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['userId'], self::INVALID_USER_ID, 'Пользователь не найден.');
    }

    public function testRequiredFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['providerName', 'providerUuid'], null, 'Значение не должно быть пустым.');
    }

    public function testStringLength(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['providerName'],
            $this->getFaker()->realText(500),
            'Длина не должна превышать 50 символов.'
        );

        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['providerUuid'],
            $this->getFaker()->realText(500),
            'Длина не должна превышать 255 символов.'
        );
    }

    public function testUniqueNameAndUuid(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUsersWithLinkedAccount::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadUsersWithLinkedAccount::getRandReferenceName());
        $linkedAccount = $user->getLinkedAccounts()->current();

        $this->command->providerName = $linkedAccount->getProviderName();
        $this->command->providerUuid = $linkedAccount->getUuid();

        $this->getValidator()->validate($this->command);
        $this->assertFieldInvalid('providerUuid', 'Провайдер привязан к другому пользователю.');
    }
}

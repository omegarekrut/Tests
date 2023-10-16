<?php

namespace Tests\Functional\Domain\User\Command\UserRegistration;

use App\Domain\SpamBlockList\Entity\SpamEmail;
use App\Domain\SpamBlockList\Entity\SpamSocialAccount;
use App\Domain\User\Command\UserRegistration\UserRegisterThroughOAuthCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\SpamBlockList\LoadSpamEmail;
use Tests\DataFixtures\ORM\SpamBlockList\LoadSpamSocialAccount;
use Tests\DataFixtures\ORM\User\LoadUsersWithLinkedAccount;
use Tests\Functional\ValidationTestCase;

/**
 * @group registration
 */
class UserRegisterThroughOAuthCommandValidationTest extends ValidationTestCase
{
    private const INVALID_DATE = 'INVALID-DATE';
    private const INVALID_EMAIL = 'INVALID-EMAIL';
    private const INVALID_GENDER = 'INVALID-GENDER';

    /** @var UserRegisterThroughOAuthCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new UserRegisterThroughOAuthCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['email'], null, 'E-mail обязателен.');
        $this->assertOnlyFieldsAreInvalid($this->command, ['providerUuid', 'providerName'], null, 'Значение не должно быть пустым.');
    }

    public function testUniqueFields(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUsersWithLinkedAccount::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadUsersWithLinkedAccount::getRandReferenceName());
        $linkedAccount = $user->getLinkedAccounts()->current();

        $this->command->email = $user->getEmailAddress();
        $this->command->providerName = $linkedAccount->getProviderName();
        $this->command->providerUuid = $linkedAccount->getUuid();

        $this->getValidator()->validate($this->command);
        $this->assertFieldInvalid('email', sprintf('Email \'%s\' уже зарегистрирован.', $user->getEmailAddress()));
        $this->assertFieldInvalid('providerUuid', 'Провайдер привязан к другому пользователю.');
    }

    public function testStringLength(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['email', 'name', 'city', 'providerUuid'],
            $this->getFaker()->realText(500),
            'Длина не должна превышать 255 символов.'
        );

        $this->assertOnlyFieldsAreInvalid($this->command, ['providerName'], $this->getFaker()->realText(500), 'Длина не должна превышать 50 символов.');
    }

    public function testDateFormat(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['dateBirthday'], self::INVALID_DATE, 'Поле дата рождение заполнено некорректно.');
    }

    public function testGenderChoice(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['gender'], self::INVALID_GENDER, 'Поле пол заполнено некорректно.');
    }

    public function testEmailValidation(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['email'], self::INVALID_EMAIL, 'Поле e-mail заполнено некорректно.');
    }

    public function testEmailInSpamBlockList(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSpamEmail::class,
        ])->getReferenceRepository();

        /** @var SpamEmail $spamEmail */
        $spamEmail = $referenceRepository->getReference(LoadSpamEmail::REFERENCE_NAME);
        $this->command->email = $spamEmail->getEmail();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('email', sprintf('Email %s заблокирован за спам.', $spamEmail->getEmail()));
    }

    public function testSocialAccountInSpamBlockList(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSpamSocialAccount::class,
        ])->getReferenceRepository();

        /** @var SpamSocialAccount $spamSocialAccount */
        $spamSocialAccount = $referenceRepository->getReference(LoadSpamSocialAccount::REFERENCE_NAME);
        $this->command->providerName = $spamSocialAccount->getProviderName();
        $this->command->providerUuid = $spamSocialAccount->getProviderUuid();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('providerUuid', sprintf(
            'Аккаунт %s номер %s заблокирован за спам.',
            $spamSocialAccount->getProviderName(),
            $spamSocialAccount->getProviderUuid()
        ));
    }
}

<?php

namespace Tests\Functional\Domain\User\Command\Profile;

use App\Domain\User\Command\Profile\UpdateBasicInformationCommand;
use App\Domain\User\Entity\User;
use App\Module\MailCheck\MailCheckClientMock;
use App\Module\PlaceCityRepository\PlaceCityRepositoryInterface;
use App\Module\VerifyMail\VerifyMailClientMock;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;
use Tests\Traits\UserGeneratorTrait;

class UpdateBasicInformationCommandValidationTest extends ValidationTestCase
{
    use UserGeneratorTrait;

    /**
     * @var UpdateBasicInformationCommand
     */
    private $command;

    /**
     * @var User $existsUser
     */
    private $existsUser;

    /**
     * @var PlaceCityRepositoryInterface $placeCityMockRepository
     */
    private $placeCityMockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->existsUser = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->command = new UpdateBasicInformationCommand($this->generateUser());
        $this->placeCityMockRepository = $this->getContainer()->get(PlaceCityRepositoryInterface::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->existsUser,
            $this->command,
            $this->placeCityMockRepository
        );

        parent::tearDown();
    }

    public function testInvalidLogin(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['login'], $this->getFaker()->realText(100), 'Максимальная длина логина 30 символов.');
        $this->assertOnlyFieldsAreInvalid($this->command, ['login'], 'x', 'Минимальная длина логина 3 символа.');
        $this->assertOnlyFieldsAreInvalid($this->command, ['login'], '', 'Логин обязателен.');
    }

    public function testInvalidEmail(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['email'], '', 'E-mail обязателен.');
        $this->assertOnlyFieldsAreInvalid($this->command, ['email'], $this->getFaker()->realText(10), 'Поле e-mail заполнено некорректно.');
        $this->assertOnlyFieldsAreInvalid($this->command, ['email'], 'test@fishsib.loc', 'На адрес test@fishsib.loc отправка почты невозможна');
    }

    public function testStringLength(): void
    {
        $this->command->name = $this->getFaker()->realText(1000);
        $this->command->cityName = $this->getFaker()->realText(1000);
        $this->command->cityCountry = $this->getFaker()->realText(1000);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('name', 'Длина не должна превышать 255 символов.');
        $this->assertFieldInvalid('cityName', 'Длина не должна превышать 255 символов.');
        $this->assertFieldInvalid('cityCountry', 'Длина не должна превышать 255 символов.');
    }

    public function testInvalidGender(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['gender'], 'gender', 'Поле пол заполнено некорректно.');
    }

    public function testInvalidBirthDate(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['birthdate'], $this->getFaker()->realText(10), 'Поле дата рождения заполнено некорректно.');
    }

    public function testUniqueLogin(): void
    {
        $this->command->login = $this->existsUser->getLogin();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('login', "К сожалению, логин '{$this->command->login}' уже занят.");
    }

    public function testUniqueEmail(): void
    {
        $this->command->email = $this->existsUser->getEmailAddress();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('email', "Email '{$this->command->email}' уже зарегистрирован.");
    }

    public function testExistCity(): void
    {
        $query = 'Новосибирск';
        $this->command->cityName = $query;

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testWithoutCity(): void
    {
        $this->command->cityName = '';

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testInvalidCity(): void
    {
        $this->placeCityMockRepository->disableSearch();

        try {
            $this->command->cityName = 'не верный город';

            $this->getValidator()->validate($this->command);

            $this->assertFieldInvalid('data.cityName', sprintf('Город с названием "%s" не был найден', $this->command->cityName));
        } finally {
            $this->placeCityMockRepository->enableSearch();
        }
    }

    public function testDisposableEmailInMailCheckClient(): void
    {
        $this->command->email = sprintf('test@%s', MailCheckClientMock::DISPOSABLE_DOMAIN);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('email', 'Использование временного адреса электронной почты запрещено');
    }

    public function testDisposableEmailInVerifyMailClient(): void
    {
        $this->command->email =  VerifyMailClientMock::DISPOSABLE_EMAIL;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('email', 'Использование временного адреса электронной почты запрещено');
    }
}

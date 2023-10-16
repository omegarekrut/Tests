<?php

namespace Tests\Functional\Domain\Ban\Command\BanUser;

use App\Domain\Ban\Command\BanUser\CreateBanUserCommand;
use Tests\DataFixtures\ORM\Ban\LoadBanUsers;
use Tests\Functional\ValidationTestCase;

/**
 * @group ban
 */
class CreateBanUserCommandValidationTest extends ValidationTestCase
{
    /** @var CreateBanUserCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new CreateBanUserCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankField(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['cause', 'user'], null, 'Значение не должно быть пустым.');
    }

    public function testInvalidLengthField(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['cause'], $this->getFaker()->realText(500), 'Значение слишком длинное. Должно быть равно 255 символам или меньше.');
    }

    public function testInvalidDatetimeField(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['expiredAt'], $this->getFaker()->realText(10), 'Значение даты и времени недопустимо.');
    }

    public function testTwiceBan(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadBanUsers::class,
        ])->getReferenceRepository();

        $existsBanUser = $referenceRepository->getReference(LoadBanUsers::BAN_USER);
        $this->command->user = $existsBanUser->getUser();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid(
            'user',
            sprintf('Пользователь уже заблокирован бессрочно по причине "%s"', $existsBanUser->getCause())
        );
    }
}

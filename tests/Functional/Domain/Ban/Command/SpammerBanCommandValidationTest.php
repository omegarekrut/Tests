<?php

namespace Tests\Functional\Domain\Ban\Command;

use App\Domain\Ban\Command\BanSpammer\SpammerBanCommand;
use App\Domain\User\Entity\User;
use Tests\Functional\ValidationTestCase;

/**
 * @group ban
 */
class SpammerBanCommandValidationTest extends ValidationTestCase
{
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new SpammerBanCommand($this->createMock(User::class), 'Ручная очистка спама');
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testEmptyUser(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['user', 'cause'], null, 'Значение не должно быть пустым.');
    }

    public function testInvalidDateFormat(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['expiredAt'], $this->getFaker()->realText(10), 'Значение даты и времени недопустимо.');
    }
}

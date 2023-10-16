<?php

namespace Tests\Functional\Domain\Ban\Command\BanIp;

use App\Domain\Ban\Command\BanIp\CreateBanIpCommand;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Ban\LoadBanIp;
use Tests\Functional\ValidationTestCase;

/**
 * @group ban
 */
class CreateBanIpCommandValidationTest extends ValidationTestCase
{
    /** @var CreateBanIpCommand */
    private $command;

    /** @var ReferenceRepository */
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadBanIp::class,
        ])->getReferenceRepository();

        $this->command = new CreateBanIpCommand();
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->command
        );

        parent::tearDown();
    }

    public function testNotBlankField(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['ipRange', 'cause'], null, 'Значение не должно быть пустым.');
    }

    public function testInvalidLengthField(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['cause'], $this->getFaker()->realText(500), 'Значение слишком длинное. Должно быть равно 255 символам или меньше.');
    }

    public function testInvalidDatetimeField(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['expiredAt'], $this->getFaker()->realText(10), 'Значение даты и времени недопустимо.');
    }
}

<?php

namespace Tests\Functional\Domain\Log\Command;

use App\Domain\Log\Command\LogActionCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\UserRole;
use Tests\Functional\ValidationTestCase;

/**
 * @group log
 */
class LogActionCommandValidationTest extends ValidationTestCase
{
    /** @var LogActionCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new LogActionCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testAlmostAllFieldsAreRequired(): void
    {
        $this->getValidator()->validate($this->command);

        foreach (['user', 'actionName', 'requestContextController', 'ip'] as $requiredFieldName) {
            $this->assertFieldInvalid($requiredFieldName, 'Значение не должно быть пустым.');
        }
    }

    public function testModeratorMustBeUser(): void
    {
        $this->command->user = $this;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('user', sprintf('Тип значения должен быть %s.', User::class));
    }

    public function testActionNameAndRequestContextShouldBeLessThan255(): void
    {
        $this->command->actionName = $this->getFaker()->realText(500);
        $this->command->requestContextController = $this->getFaker()->realText(500);
        $this->command->requestContextReferer = $this->getFaker()->realText(500);

        $this->getValidator()->validate($this->command);

        foreach (['actionName', 'requestContextController', 'requestContextReferer'] as $fieldName) {
            $this->assertFieldInvalid($fieldName, 'Значение слишком длинное. Должно быть равно 255 символам или меньше.');
        }
    }

    public function testIpShouldBeLessThan20(): void
    {
        $this->command->ip = $this->getFaker()->realText(500);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('ip', 'Значение слишком длинное. Должно быть равно 20 символам или меньше.');
    }

    public function testActionNameMustBeRequiredAttention(): void
    {
        $this->command->actionName = 'Action\Free\Action';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('actionName', "Экшен \"{$this->command->actionName }\" не требует логирования");
    }

    public function testActionParametersMustArrayOfScalar(): void
    {
        $this->command->actionParameters = [
            'not scalar' => [],
        ];

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('actionParameters[not scalar]', 'Тип значения должен быть scalar.');
    }

    public function testCommandFilledWithCorrectDataShouldNotCauseErrors(): void
    {
        $this->command->user = $this->createMock(User::class);

        $this->command->ip = '127.0.0.1';
        $this->command->actionName = 'Some\\CreateCommand';
        $this->command->actionParameters = [];
        $this->command->requestContextController = 'Some\\Controller::action';
        $this->command->requestContextReferer = 'http://foo.bar';

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}

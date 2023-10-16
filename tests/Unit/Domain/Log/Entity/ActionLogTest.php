<?php

namespace Tests\Unit\Domain\Log\Entity;

use App\Domain\Log\Entity\ActionLog;
use App\Domain\Log\Entity\ValueObject\LoggingAction;
use App\Domain\Log\Entity\ValueObject\LoggingRequestContext;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\UserRole;
use LogicException;
use Tests\Unit\TestCase;

/**
 * @group log
 */
class ActionLogTest extends TestCase
{
    private LoggingAction $validAction;
    private LoggingRequestContext $validRequestContext;
    private string $validIp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validAction = new LoggingAction('Some\\Required\\Attention\\CreateAction', ['some parameter' => 'value']);
        $this->validRequestContext = new LoggingRequestContext('SomeValidController::test', 'http://foo.bar');
        $this->validIp = '127.0.0.1';
    }

    public function testLogCanBeCreatedForModerators(): void
    {
        $user = $this->createUserWithRoles(UserRole::moderator());
        $log = new ActionLog($user, $this->validAction, $this->validRequestContext, $this->validIp);

        $this->assertSame($user, $log->getUser());
        $this->assertSame($this->validAction, $log->getAction());
        $this->assertSame($this->validRequestContext, $log->getRequestContext());
        $this->assertEquals($this->validIp, $log->getIp());
    }

    /**
     * @dataProvider getActionsRequireAttention
     */
    public function testLogCanBeCreatedForActionRequiredAttention(string $actionName): void
    {
        $moderator = $this->createUserWithRoles(UserRole::moderator());
        $actionRequireAttention = new LoggingAction($actionName);

        $log = new ActionLog($moderator, $actionRequireAttention, $this->validRequestContext, $this->validIp);

        $this->assertSame($actionRequireAttention, $log->getAction());
    }

    /**
     * @return string[][]
     */
    public function getActionsRequireAttention(): array
    {
        return [
            ['\CreateStufCommand'],
            ['\UpdateSomeStuf'],
            ['\EditSomeStuf'],
            ['\ChangeSomeStuf'],
            ['\DeleteSomeStuf'],
            ['\DropSomeStuf'],
            ['\RemoveSomeStuf'],
            ['\HideSomeStuf'],
            ['\RestoreSomeStuf'],
            ['\SendWarningSomeStuf'],
        ];
    }

    /**
     * @dataProvider getActionsNotRequireAttention
     */
    public function testLogCantBeCreatedWithActionWhoNotRequiredAttention(string $actionName): void
    {
        $moderator = $this->createUserWithRoles(UserRole::moderatorABM());
        $actionNotRequireAttention = new LoggingAction($actionName);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Action should require attention');

        new ActionLog($moderator, $actionNotRequireAttention, $this->validRequestContext, $this->validIp);
    }

    /**
     * @return string[][]
     */
    public function getActionsNotRequireAttention(): array
    {
        return [
            ['plain text action'],
            ['\ViewSomeStuf'],
            ['\SendSomeEmail'],
            ['UpdateFromGlobalNamespace'],
        ];
    }

    private function createUserWithRoles(string ...$roles): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->method('getRoles')
            ->willReturn($roles);

        return $stub;
    }
}

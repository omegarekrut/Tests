<?php

namespace Tests\Functional\Domain\Log\Command\Handler;

use App\Domain\Log\Command\LogActionCommand;
use App\Domain\Log\Entity\ActionLog;
use App\Domain\Log\Repository\ActionLogRepository;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\Functional\TestCase;

/**
 * @group log
 */
class LogActionHandlerTest extends TestCase
{
    /** @var User */
    private $user;
    /** @var ActionLogRepository */
    private $actionLogRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadModeratorUser::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);
        $this->actionLogRepository = $this->getEntityManager()->getRepository(ActionLog::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->actionLogRepository
        );

        parent::tearDown();
    }

    public function testAfterHandlingActionShouldBeSavedInLog(): void
    {
        $command = new LogActionCommand();
        $command->user = $this->user;
        $command->ip = '127.0.0.1';
        $command->actionName = 'Some\\UpdateCommand';
        $command->actionParameters = ['foo' => 'bar'];
        $command->requestContextController = 'Some\\Controller::action';
        $command->requestContextReferer = 'http://foo.bar';

        $this->getCommandBus()->handle($command);

        /** @var ActionLog|null $actualLog */
        $actualLog = $this->actionLogRepository->findOneBy([], ['createdAt' => 'desc']);

        $this->assertNotEmpty($actualLog);
        $this->assertTrue($command->user === $actualLog->getUser());
        $this->assertEquals($command->ip, $actualLog->getIp());
        $this->assertEquals($command->actionName, $actualLog->getAction()->getName());
        $this->assertEquals($command->actionParameters, $actualLog->getAction()->getParameters());
        $this->assertEquals($command->requestContextController, $actualLog->getRequestContext()->getController());
        $this->assertEquals($command->requestContextReferer, $actualLog->getRequestContext()->getReferer());
    }
}

<?php

namespace Tests\Unit\Domain\Ban\Command;

use App\Domain\Ban\Command\BanIp\CreateBanIpCommand;
use App\Domain\Ban\Command\BanIp\UpdateBanIpCommand;
use App\Domain\Ban\Command\BanSpammer\Handler\SpammerBanHandler;
use App\Domain\Ban\Command\BanSpammer\SpammerBanCommand;
use App\Domain\Ban\Command\BanUser\CreateBanUserCommand;
use App\Domain\Ban\Command\BanUser\UpdateBanUserCommand;
use App\Domain\Ban\Entity\BanIp;
use App\Domain\Ban\Entity\BanUser;
use App\Domain\Ban\Repository\BanIpRepository;
use App\Domain\Ban\Repository\BanUserRepository;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use Carbon\Carbon;
use League\Tactician\CommandBus;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\TestCase;

/**
 * @group ban
 */
class SpammerBanCommandHandlerTest extends TestCase
{
    /** @var User */
    private $user;

    /** @var BanUserRepository */
    private $emptyBanUserRepository;

    /** @var BanIpRepository */
    private $emptyBanIpRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser(1, '8.8.8.8');
        $this->emptyBanUserRepository = $this->createBanUserRepository();
        $this->emptyBanIpRepository = $this->createBanIpRepository();
    }

    public function testCreateBan(): void
    {
        $timeRunTest = Carbon::now();

        $commandBus = new CommandBusMock();

        $command = new SpammerBanCommand($this->user, 'Каким способом пользователь определен, как спамер');
        $handler = new SpammerBanHandler($this->emptyBanUserRepository, $this->emptyBanIpRepository);
        $handler->setCommandBus($commandBus);

        $handler->handle($command);

        $this->assertCount(2, $commandBus->getAllHandledCommands());

        $createBanUserCommands = $commandBus->getAllHandledCommandsOfClass(CreateBanUserCommand::class);
        $createBanIpCommands = $commandBus->getAllHandledCommandsOfClass(CreateBanIpCommand::class);

        self::assertCount(1, $createBanUserCommands);
        self::assertCount(1, $createBanIpCommands);

        $this->assertEquals($this->user, $createBanUserCommands[0]->user);
        $this->assertEquals('Каким способом пользователь определен, как спамер', $createBanUserCommands[0]->cause);
        $this->assertNull($createBanUserCommands[0]->expiredAt);

        $this->assertEquals($this->user->getLastVisit()->getLastVisitIp(), $createBanIpCommands[0]->ipRange);
        $this->assertEquals('Каким способом пользователь определен, как спамер', $createBanIpCommands[0]->cause);
        $this->assertEquals(7, $timeRunTest->diff($createBanIpCommands[0]->expiredAt)->d);
    }

    public function testUpdateBanUser(): void
    {
        $existsBanUser = $this->createMock(BanUser::class);
        $banUserRepository = $this->createBanUserRepository($existsBanUser);

        $commandBus = new CommandBusMock();

        $command = new SpammerBanCommand($this->user, 'Каким способом пользователь определен, как спамер');
        $handler = new SpammerBanHandler($banUserRepository, $this->emptyBanIpRepository);
        $handler->setCommandBus($commandBus);

        $handler->handle($command);

        $this->assertCount(2, $commandBus->getAllHandledCommands());

        $updateBanUserCommands = $commandBus->getAllHandledCommandsOfClass(UpdateBanUserCommand::class);

        self::assertCount(1, $updateBanUserCommands);

        $this->assertEquals($existsBanUser, $updateBanUserCommands[0]->getBanUser());
        $this->assertEquals('Каким способом пользователь определен, как спамер', $updateBanUserCommands[0]->cause);
        $this->assertNull($updateBanUserCommands[0]->expiredAt);
    }

    public function testUpdateBanIp(): void
    {
        $existsBanIp = $this->createMock(BanIp::class);
        $banIpRepository = $this->createBanIpRepository($existsBanIp);

        $commandBus = new CommandBusMock();

        $command = new SpammerBanCommand($this->user, 'Каким способом пользователь определен, как спамер');
        $handler = new SpammerBanHandler($this->emptyBanUserRepository, $banIpRepository);
        $handler->setCommandBus($commandBus);

        $handler->handle($command);

        $this->assertCount(2, $commandBus->getAllHandledCommands());

        $updateBanIpCommands = $commandBus->getAllHandledCommandsOfClass(UpdateBanIpCommand::class);

        self::assertCount(1, $updateBanIpCommands);

        $this->assertEquals($existsBanIp, $updateBanIpCommands[0]->getBanIp());
        $this->assertEquals('Каким способом пользователь определен, как спамер', $updateBanIpCommands[0]->cause);
    }

    private function createBanUserRepository(?BanUser $banUser = null): BanUserRepository
    {
        $stub = $this->createMock(BanUserRepository::class);
        $stub
            ->method('findActiveByUserId')
            ->willReturn($banUser);

        return $stub;
    }

    private function createBanIpRepository(?BanIp $banIp = null): BanIpRepository
    {
        $stub = $this->createMock(BanIpRepository::class);
        $stub
            ->method('findActiveByIp')
            ->willReturn($banIp);

        return $stub;
    }

    private function createCommandBus(...$commandHandlers): CommandBus
    {
        $stub = $this->createMock(CommandBus::class);

        $stub
            ->expects($this->exactly(count($commandHandlers)))
            ->method('handle')
            ->will(...array_map(static fn(callable $callback): ReturnCallback => new ReturnCallback($callback), [...$commandHandlers]));

        return $stub;
    }

    private function createUser(int $id, string $ip): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->method('getId')
            ->willReturn($id);

        $stub
            ->method('getLastVisit')
            ->willReturn(new LastVisit($ip, new \DateTime()));

        return $stub;
    }
}

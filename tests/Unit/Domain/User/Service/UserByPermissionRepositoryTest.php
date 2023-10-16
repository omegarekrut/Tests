<?php

namespace Tests\Unit\Domain\User\Service;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserByPermissionRepository;
use App\Domain\User\Repository\UserRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Tests\Unit\TestCase;

/**
 * @group user
 */
class UserByPermissionRepositoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFindWithPermissionForSendComplaintWithoutAccess(): void
    {
        $service = new UserByPermissionRepository(
            $this->getMockAccessDecisionManager(false),
            $this->getMockUserRepository([])
        );

        $users = $service->findWithPermissionForSendComplaint();

        $this->assertEmpty($users);
    }

    public function testFindWithPermissionForSendComplaintWithAccess(): void
    {
        $expectedUsers = [$this->getMockUser()];

        $service = new UserByPermissionRepository(
            $this->getMockAccessDecisionManager(true),
            $this->getMockUserRepository($expectedUsers),
        );

        $users = $service->findWithPermissionForSendComplaint();

        $this->assertContains($expectedUsers[0], $users);
    }

    private function getMockAccessDecisionManager(bool $isGranted): AccessDecisionManagerInterface
    {
        $mock = $this->prophesize(AccessDecisionManagerInterface::class);

        $mock
            ->decide(Argument::any(), Argument::any())
            ->willReturn($isGranted);

        return $mock->reveal();
    }

    /**
     * @param User[]
     */
    private function getMockUserRepository(array $users): UserRepository
    {
        $mock = $this->prophesize(UserRepository::class);

        $mock
            ->findOneUserForEachGroup()
            ->willReturn([$this->getMockUser()]);

        $mock
            ->findByGroups(Argument::any())
            ->willReturn($users);

        return $mock->reveal();
    }

    private function getMockUser(): User
    {
        $mock = $this->prophesize(User::class);

        $mock
            ->getId()
            ->willReturn(1);

        $mock
            ->getRoles()
            ->willReturn(['admin']);

        $mock
            ->getGroup()
            ->willReturn('admin');

        return $mock->reveal();
    }
}

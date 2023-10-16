<?php

namespace Tests\Unit\Logger\MonologProcessor;

use App\Domain\User\Entity\User;
use App\Logger\MonologProcessor\UserProcessor;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\Unit\TestCase;

/**
 * @group log
 */
class UserProcessorTest extends TestCase
{
    public function testPrepareRecord()
    {
        $user = $this->createUser(1, 'username');

        $processor = new UserProcessor($this->getTokenStorageMock($user));
        $record = $processor->processRecord([
            'extra' => [],
        ]);
        $extraRequest = explode(', ', $record['extra']['User']);

        $this->assertStringContainsString('/users/profile/'.$user->getId().'/', $extraRequest[0]);
        $this->assertEquals('id: '.$user->getId(), $extraRequest[1]);
        $this->assertEquals('login: '.$user->getUserName(), $extraRequest[2]);
    }

    private function getTokenStorageMock(User $user = null): TokenStorage
    {
        $token = new UsernamePasswordToken(
            $user,
            $user->getPassword(),
            'main',
            $user->getRoles()

        );
        $visitorMock = $this->createMock(TokenStorage::class);
        $visitorMock
            ->method('getToken')
            ->will($this->returnValue($token));

        return $visitorMock;
    }

    private function createUser(int $id, string $username): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->method('getId')
            ->willReturn($id);
        $stub
            ->method('getUsername')
            ->willReturn($username);

        return $stub;
    }
}

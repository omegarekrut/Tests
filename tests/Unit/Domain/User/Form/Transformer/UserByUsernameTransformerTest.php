<?php

namespace Tests\Unit\Domain\User\Form\Transformer;

use App\Domain\User\Entity\User;
use App\Domain\User\Form\Transformer\UserByUsernameTransformer;
use App\Domain\User\Repository\UserRepository;
use Tests\Unit\TestCase;

class UserByUsernameTransformerTest extends TestCase
{
    public function testTransformation(): void
    {
        $user = $this->createUser('username');
        $transformer = new UserByUsernameTransformer($this->createUserRepository($user));

        $asString = $transformer->transform($user);
        $this->assertEquals('username', $asString);

        $asObject = $transformer->reverseTransform($asString);
        $this->assertEquals($user, $asObject);
    }

    public function testEmptyData(): void
    {
        $transformer = new UserByUsernameTransformer($this->createMock(UserRepository::class));

        $this->assertEmpty($transformer->transform(null));
        $this->assertEmpty($transformer->reverseTransform(null));
    }

    private function createUser(string $username): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->expects($this->any())
            ->method('getUsername')
            ->willReturn($username);

        return $stub;
    }

    private function createUserRepository(?User $user = null): UserRepository
    {
        $stub = $this->createMock(UserRepository::class);

        if ($user) {
            $stub
                ->expects($this->once())
                ->method('findByUsername')
                ->with($user->getUsername())
                ->willReturn($user);
        } else {
            $stub
                ->expects($this->never())
                ->method('findByUsername');
        }

        return $stub;
    }
}

<?php

namespace Tests\Unit\Domain\User\Validator\Constraint;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Validator\Constraint\UserCanBeAuthenticated;
use App\Domain\User\Validator\Constraint\UserCanBeAuthenticatedValidator;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\UserPasswordEncoderMock;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class UserCanBeAuthenticatedTest extends TestCase
{
    private const LOGIN_OR_EMAIL_AND_PASSWORD = ['loginOrEmail', 'password'];

    public function testIsInvalidUsernameOrPassword(): void
    {
        $executionContext = new ValidatorExecutionContextMock();
        $validator = new UserCanBeAuthenticatedValidator($this->createUserRepository(), new UserPasswordEncoderMock());
        $validator->initialize($executionContext);

        $validator->validate(self::LOGIN_OR_EMAIL_AND_PASSWORD, new UserCanBeAuthenticated());

        $this->assertTrue($executionContext->hasViolations());
    }

    public function testNoViolationsForFoundedUserByUsernameAndPassword(): void
    {
        $executionContext = new ValidatorExecutionContextMock();
        $validator = new UserCanBeAuthenticatedValidator($this->createUserRepository($this->generateUser()), new UserPasswordEncoderMock());
        $validator->initialize($executionContext);

        $validator->validate(self::LOGIN_OR_EMAIL_AND_PASSWORD, new UserCanBeAuthenticated());

        $this->assertFalse($executionContext->hasViolations());
    }

    public function testExpectExceptionForInvalidConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $validator = new UserCanBeAuthenticatedValidator($this->createUserRepository(), new UserPasswordEncoderMock());
        $validator->validate(self::LOGIN_OR_EMAIL_AND_PASSWORD, $this->createMock(Constraint::class));
    }

    private function createUserRepository(?User $user = null): UserRepository
    {
        $stub = $this->createMock(UserRepository::class);
        $stub
            ->method('findOneByLoginOrEmail')
            ->willReturn($user);

        return $stub;
    }
}

<?php

namespace Tests\Unit\Domain\Ban\Validator;

use App\Domain\Ban\Entity\BanUser;
use App\Domain\Ban\Repository\BanUserRepository;
use App\Domain\Ban\Validator\Constraint\UserIsNotBanned;
use App\Domain\Ban\Validator\Constraint\UserWithLoginOrEmailIsNotBanned;
use App\Domain\Ban\Validator\Constraint\UserWithLoginOrEmailIsNotBannedValidator;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class UserWithLoginOrEmailIsNotBannedValidatorTest extends TestCase
{
    private const LOGIN_OR_EMAIL = 'loginOrEmail';

    public function testConstraintMustBeRightInstance(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $validator = new UserWithLoginOrEmailIsNotBannedValidator($this->createUserRepository($this->createUser()), $this->createBanUserRepository($this->createBan()));

        $validator->validate(self::LOGIN_OR_EMAIL, new UserIsNotBanned());
    }

    public function testValidationPassWhenLoginOrEmailNotDefined(): void
    {
        $executionContext = new ValidatorExecutionContextMock();

        $validator = new UserWithLoginOrEmailIsNotBannedValidator($this->createUserRepository($this->createUser()), $this->createBanUserRepository($this->createBan()));
        $validator->initialize($executionContext);

        $validator->validate('', new UserWithLoginOrEmailIsNotBanned());

        $this->assertFalse($executionContext->hasViolations());
    }

    public function testValidationFailWhenUserIsBanned(): void
    {
        $executionContext = new ValidatorExecutionContextMock();

        $validator = new UserWithLoginOrEmailIsNotBannedValidator($this->createUserRepository($this->createUser()), $this->createBanUserRepository($this->createBan()));
        $validator->initialize($executionContext);

        $validator->validate(self::LOGIN_OR_EMAIL, new UserWithLoginOrEmailIsNotBanned());

        $this->assertTrue($executionContext->hasViolations());
    }

    public function testValidationPassForNotExistingUser(): void
    {
        $executionContext = new ValidatorExecutionContextMock();

        $validator = new UserWithLoginOrEmailIsNotBannedValidator($this->createUserRepository(), $this->createBanUserRepository($this->createBan()));
        $validator->initialize($executionContext);

        $validator->validate(self::LOGIN_OR_EMAIL, new UserWithLoginOrEmailIsNotBanned());

        $this->assertFalse($executionContext->hasViolations());
    }

    public function testValidationPassForNotBannedUser(): void
    {
        $executionContext = new ValidatorExecutionContextMock();

        $validator = new UserWithLoginOrEmailIsNotBannedValidator($this->createUserRepository($this->createUser()), $this->createBanUserRepository());
        $validator->initialize($executionContext);

        $validator->validate(self::LOGIN_OR_EMAIL, new UserWithLoginOrEmailIsNotBanned());

        $this->assertFalse($executionContext->hasViolations());
    }

    private function createUserRepository(?User $user = null): UserRepository
    {
        $stub = $this->createMock(UserRepository::class);
        $stub
            ->method('findOneByLoginOrEmail')
            ->willReturn($user);

        return $stub;
    }

    private function createBanUserRepository(?BanUser $userBan = null): BanUserRepository
    {
        $stub = $this->createMock(BanUserRepository::class);
        $stub
            ->method('findActiveByUserId')
            ->willReturn($userBan);

        return $stub;
    }

    private function createUser(): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->method('getId')
            ->willReturn(2);

        return $stub;
    }

    private function createBan(): BanUser
    {
        return $this->createMock(BanUser::class);
    }
}

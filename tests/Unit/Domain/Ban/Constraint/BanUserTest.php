<?php

namespace Tests\Unit\Domain\Ban\Constraint;

use App\Domain\Ban\Command\BanUser\UpdateBanUserCommand;
use App\Domain\Ban\Entity\BanUser;
use App\Domain\Ban\Repository\BanUserRepository;
use App\Domain\Ban\Validator\Constraint\UserIsNotBanned;
use App\Domain\Ban\Validator\Constraint\UserIsNotBannedValidator;
use App\Domain\User\Entity\User;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Tests\Unit\TestCase;

/**
 * @group ban
 * @group ban-constraint
 */
class BanUserTest extends TestCase
{
    public function testUnsupportedValidation(): void
    {
        $validator = new UserIsNotBannedValidator($this->createMock(BanUserRepository::class));
        $validator->initialize($this->createExecutionContext(false, 'user'));
        $validator->validate('username', new UserIsNotBanned());
    }

    public function testExistsUserOnCreate(): void
    {
        $validator = new UserIsNotBannedValidator($this->createBanUserRepository(1));
        $validator->initialize($this->createExecutionContext(true));
        $validator->validate($this->createUser(2), new UserIsNotBanned());
    }

    public function testValidUpdateCommand(): void
    {
        $validator = new UserIsNotBannedValidator($this->createBanUserRepository(2));
        $validator->initialize($this->createExecutionContext(false));
        $validator->validate($this->createUpdateBanUserCommand(), new UserIsNotBanned());
    }

    private function createBanUserRepository(int $userId): BanUserRepository
    {
        $mock = $this->createMock(BanUserRepository::class);

        $mock->method('findActiveByUserId')
            ->willReturn($this->createEntity($userId));

        return $mock;
    }

    private function createUpdateBanUserCommand(): UpdateBanUserCommand
    {
        return new UpdateBanUserCommand($this->createEntity(2));
    }

    private function createUser($userId)
    {
        $mockUser = $this->createMock(User::class);

        $mockUser
            ->method('getId')
            ->willReturn($userId);

        return $mockUser;
    }

    private function createEntity(int $id): BanUser
    {
        $mock = $this->createMock(BanUser::class);

        $mock
            ->method('getId')
            ->willReturn($id);

        $mock
            ->method('getUser')
            ->willReturn($this->createUser($id));

        return $mock;
    }

    private function createExecutionContext(bool $called): ExecutionContext
    {
        $expects = $called ? $this->once() : $this->never();
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $violationBuilder
            ->expects(clone $expects)
            ->method('setParameters')
            ->willReturn($violationBuilder);

        $violationBuilder
            ->expects(clone $expects)
            ->method('addViolation');

        $stub = $this->createMock(ExecutionContext::class);
        $stub
            ->expects(clone $expects)
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        return $stub;
    }
}

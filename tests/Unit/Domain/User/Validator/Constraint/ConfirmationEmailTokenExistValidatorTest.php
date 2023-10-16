<?php

namespace Tests\Unit\Domain\User\Validator\Constraint;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Validator\Constraint\ConfirmationEmailTokenExist;
use App\Domain\User\Validator\Constraint\ConfirmationEmailTokenExistValidator;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class ConfirmationEmailTokenExistValidatorTest extends TestCase
{
    public function testIsInvalidToken(): void
    {
        $executionContext = new ValidatorExecutionContextMock();
        $validator = new ConfirmationEmailTokenExistValidator($this->createUserRepository());
        $validator->initialize($executionContext);

        $validator->validate('invalid-token', new ConfirmationEmailTokenExist());

        $this->assertTrue($executionContext->hasViolations());
    }

    public function testNoViolationsForFoundedUserByConfirmationEmailTokenToken(): void
    {
        $executionContext = new ValidatorExecutionContextMock();
        $validator = new ConfirmationEmailTokenExistValidator($this->createUserRepository($this->createMock(User::class)));
        $validator->initialize($executionContext);

        $validator->validate('valid-token', new ConfirmationEmailTokenExist());

        $this->assertFalse($executionContext->hasViolations());
    }

    private function createUserRepository(?User $user = null): UserRepository
    {
        $stub = $this->createMock(UserRepository::class);
        $stub
            ->method('findOneByConfirmationEmailToken')
            ->willReturn($user);

        return $stub;
    }
}

<?php

namespace Tests\Unit\Domain\User\Validator\Constraint;

use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Validator\Constraint\UserExist;
use App\Domain\User\Validator\Constraint\UserExistValidator;
use Symfony\Component\Validator\Context\ExecutionContext;
use Tests\Unit\TestCase;

/**
 * @group reset-password
 */
class UserExistTest extends TestCase
{
    public function testValidate()
    {
        $validator = new UserExistValidator($this->getMockUserRepository());
        $validator->initialize($this->getMockExecutionContext());
        $validator->validate('test', new UserExist());
    }

    private function getMockUserRepository(): UserRepository
    {
        $mock = $this->createMock(UserRepository::class);

        $mock->method('findOneByLoginOrEmail')
            ->willReturn(null);

        return $mock;
    }

    private function getMockExecutionContext(): ExecutionContext
    {
        $stub = $this->createMock(ExecutionContext::class);
        $stub
            ->expects($this->once())
            ->method('addViolation');

        return $stub;
    }
}

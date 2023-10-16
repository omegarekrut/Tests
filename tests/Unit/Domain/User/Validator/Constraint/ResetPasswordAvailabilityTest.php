<?php

namespace Tests\Unit\Domain\User\Validator\Constraint;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\Token;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Validator\Constraint\ResetPasswordAvailability;
use App\Domain\User\Validator\Constraint\ResetPasswordAvailabilityValidator;
use App\Util\Pluralization\Pluralization;
use Carbon\Carbon;
use Symfony\Component\Validator\Context\ExecutionContext;
use Tests\Unit\TestCase;

/**
 * @group reset-password
 */
class ResetPasswordAvailabilityTest extends TestCase
{
    public function testValidate()
    {
        $validator = new ResetPasswordAvailabilityValidator($this->getMockUserRepository(), $this->getMockPluralization());
        $validator->initialize($this->getMockExecutionContext());
        $validator->validate('test', new ResetPasswordAvailability());
    }

    private function getMockUserRepository(): UserRepository
    {
        $mock = $this->createMock(UserRepository::class);

        $mock->method('findOneByLoginOrEmail')
            ->willReturn($this->getMockUser());

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

    private function getMockUser(): User
    {
        $stub = $this->createMock(User::class);

        $stub
            ->method('getResetPasswordToken')
            ->willReturn(new Token('token', Carbon::now()));

        return $stub;
    }

    private function getMockPluralization(): Pluralization
    {
        $stub = $this->createMock(Pluralization::class);

        $stub
            ->method('pluralize')
            ->willReturn('string');

        return $stub;
    }
}

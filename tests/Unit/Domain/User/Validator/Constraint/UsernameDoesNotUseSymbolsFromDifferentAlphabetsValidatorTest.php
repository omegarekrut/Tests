<?php

namespace Tests\Unit\Domain\User\Validator\Constraint;

use App\Domain\User\Validator\Constraint\UserExist;
use App\Domain\User\Validator\Constraint\UsernameDoesNotUseSymbolsFromDifferentAlphabets;
use App\Domain\User\Validator\Constraint\UsernameDoesNotUseSymbolsFromDifferentAlphabetsValidator;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class UsernameDoesNotUseSymbolsFromDifferentAlphabetsValidatorTest extends TestCase
{
    /** @var UsernameDoesNotUseSymbolsFromDifferentAlphabetsValidator  */
    private $usernameDoesNotUseSymbolsFromDifferentAlphabetsValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usernameDoesNotUseSymbolsFromDifferentAlphabetsValidator = new UsernameDoesNotUseSymbolsFromDifferentAlphabetsValidator();
    }

    protected function tearDown(): void
    {
        unset($this->usernameDoesNotUseSymbolsFromDifferentAlphabetsValidator);

        parent::tearDown();
    }

    public function testConstraintMustBeRightInstance(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->usernameDoesNotUseSymbolsFromDifferentAlphabetsValidator->validate('username', new UserExist());
    }

    /**
     * @dataProvider getUsername
     */
    public function testUsernameContainDifferentAlphabet(string $username, bool $isNotValid): void
    {
        $constraint = new UsernameDoesNotUseSymbolsFromDifferentAlphabets();
        $executionContext = new ValidatorExecutionContextMock();

        $this->usernameDoesNotUseSymbolsFromDifferentAlphabetsValidator->initialize($executionContext);

        $this->usernameDoesNotUseSymbolsFromDifferentAlphabetsValidator->validate($username, $constraint);

        $this->assertEquals($isNotValid, $executionContext->hasViolations());
    }

    public static function getUsername(): \Generator
    {
        yield [
            '',
            false,
        ];

        yield [
            'username',
            false,
        ];

        yield [
            'Пользователь',
            false,
        ];

        yield [
            'ЮзерUsername',
            true,
        ];
    }
}

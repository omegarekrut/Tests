<?php

namespace Tests\Unit\Domain\User\Validator\Constraint;

use App\Domain\User\Validator\Constraint\UsernameDoesNotContainForbiddenSymbols;
use App\Domain\User\Validator\Constraint\UsernameDoesNotContainForbiddenSymbolsValidator;
use Generator;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class UsernameDoesNotContainForbiddenSymbolsValidatorTest extends TestCase
{
    private const ALLOWED_ENGLISH_ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const ALLOWED_RUSSIAN_ALPHABET = 'абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';
    private const ALLOWED_SYMBOLS = '!$()-.^_~';
    private const ALLOWED_NUMBERS = '1234567890';

    private const FORBIDDEN_SYMBOLS = ['@', '#', '%', '&', '*', '+', '€', 'ü', 'Ë', 'Ø', '¶'];

    private UsernameDoesNotContainForbiddenSymbolsValidator $usernameDoesNotContainForbiddenSymbolsValidator;
    private UsernameDoesNotContainForbiddenSymbols $constraint;
    private ValidatorExecutionContextMock $executionContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new UsernameDoesNotContainForbiddenSymbols();
        $this->executionContext = new ValidatorExecutionContextMock();

        $this->usernameDoesNotContainForbiddenSymbolsValidator = new UsernameDoesNotContainForbiddenSymbolsValidator();
        $this->usernameDoesNotContainForbiddenSymbolsValidator->initialize($this->executionContext);
    }

    protected function tearDown(): void
    {
        unset(
            $this->constraint,
            $this->executionContext,
            $this->usernameDoesNotContainForbiddenSymbolsValidator,
        );

        parent::tearDown();
    }

    public function testConstraintMustBeRightInstance(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->usernameDoesNotContainForbiddenSymbolsValidator->validate('username', $this->createMock(Constraint::class));
    }

    /**
     * @dataProvider getUsernamesWithAllowedSymbols
     */
    public function testAllowedSymbolsMustBeConsideredAsValid(string $usernameWithAllowedSymbols): void
    {
        $this->usernameDoesNotContainForbiddenSymbolsValidator->validate($usernameWithAllowedSymbols, $this->constraint);

        $this->assertEquals(false, $this->executionContext->hasViolations());
    }

    public static function getUsernamesWithAllowedSymbols(): Generator
    {
        yield [self::ALLOWED_ENGLISH_ALPHABET];

        yield [self::ALLOWED_RUSSIAN_ALPHABET];

        yield [self::ALLOWED_SYMBOLS];

        yield [self::ALLOWED_NUMBERS];
    }

    /**
     * @dataProvider getUsernamesWithForbiddenSymbols
     */
    public function testForbiddenSymbolsMustBeConsideredAsInvalid(string $usernameWithForbiddenSymbols): void
    {
        $this->usernameDoesNotContainForbiddenSymbolsValidator->validate($usernameWithForbiddenSymbols, $this->constraint);

        $this->assertEquals(true, $this->executionContext->hasViolations());
    }

    public static function getUsernamesWithForbiddenSymbols(): Generator
    {
        foreach (self::FORBIDDEN_SYMBOLS as $forbiddenSymbol) {
            yield [$forbiddenSymbol];
        }
    }

    public function testNotUtfStringMustBeConsideredAsInvalid(): void
    {
        $this->usernameDoesNotContainForbiddenSymbolsValidator->validate(chr(129), $this->constraint);

        $this->assertEquals(true, $this->executionContext->hasViolations());
    }
}

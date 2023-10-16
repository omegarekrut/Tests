<?php

namespace Tests\Unit\Module\CapsLockChecker\Constraint;

use App\Module\CapsLockChecker\Constraint\TextDoesNotContainTooMuchUpperCase;
use App\Module\CapsLockChecker\Constraint\TextDoesNotContainTooMuchUpperCaseValidator;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class TextDoesNotContainTooMuchUpperCaseTest extends TestCase
{
    private const VALID_EMPTY_TEXT = '';
    private const VALID_TEXT = 'Некоторый валидный текст';
    private const VALID_SHORT_TEXT = 'КОРОТКИЙ';
    private const INVALID_TEXT = 'НЕКОТОРЫЙ ТЕКСТ В ВЕРХНЕМ РЕГИСТРЕ';

    /**
     * @dataProvider getTexts
     */
    public function testValidation(string $text, bool $isValid): void
    {
        $executionContext = new ValidatorExecutionContextMock();

        $validator = new TextDoesNotContainTooMuchUpperCaseValidator();

        $validator->initialize($executionContext);
        $validator->validate($text, new TextDoesNotContainTooMuchUpperCase());

        $this->assertNotEquals($executionContext->hasViolations(), $isValid);
    }

    public static function getTexts(): \Generator
    {
        yield [
            self::VALID_EMPTY_TEXT,
            true,
        ];

        yield [
            self::VALID_TEXT,
            true,
        ];

        yield [
            self::VALID_SHORT_TEXT,
            true,
        ];

        yield [
            self::INVALID_TEXT,
            false,
        ];
    }
}

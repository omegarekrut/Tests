<?php

namespace Tests\Unit\Domain\User\Validator\Constraint;

use App\Domain\User\Command\Subscription\SubscribeToNewsletterCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Generator\SubscribeNewsletterHashGenerator;
use App\Domain\User\Validator\Constraint\SubscribeNewsletterHashIsCorrect;
use App\Domain\User\Validator\Constraint\SubscribeNewsletterHashIsCorrectValidator;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class SubscribeNewsletterHashIsCorrectTest extends TestCase
{
    private const INVALID_NEWS_LETTER_HASH ='invalid-news-letter-hash';
    private const VALID_NEWS_LETTER_HASH ='valid-news-letter-hash';

    public function testNoViolationsForValidHash(): void
    {
        $executionContext = new ValidatorExecutionContextMock();

        $validator = new SubscribeNewsletterHashIsCorrectValidator($this->getSubscribeNewsletterHashGeneratorMock());

        $validator->initialize($executionContext);
        $validator->validate($this->getSubscribeToNewsletterCommandMock(self::VALID_NEWS_LETTER_HASH), new SubscribeNewsletterHashIsCorrect());

        $this->assertFalse($executionContext->hasViolations());
    }

    public function testIsInvalidHash(): void
    {
        $executionContext = new ValidatorExecutionContextMock();

        $validator = new SubscribeNewsletterHashIsCorrectValidator($this->getSubscribeNewsletterHashGeneratorMock());

        $validator->initialize($executionContext);
        $validator->validate($this->getSubscribeToNewsletterCommandMock(self::INVALID_NEWS_LETTER_HASH), new SubscribeNewsletterHashIsCorrect());

        $this->assertTrue($executionContext->hasViolations());
    }

    private function getSubscribeNewsletterHashGeneratorMock(): SubscribeNewsletterHashGenerator
    {
        $stub = $this->createMock(SubscribeNewsletterHashGenerator::class);
        $stub
            ->method('generate')
            ->willReturn(self::VALID_NEWS_LETTER_HASH);

        return $stub;
    }

    private function getSubscribeToNewsletterCommandMock(string $hash): SubscribeToNewsletterCommand
    {
        $stub = $this->createMock(SubscribeToNewsletterCommand::class);
        $stub
            ->method('getUser')
            ->willReturn($this->getUserMock());
        $stub
            ->method('getHash')
            ->willReturn($hash);

        return $stub;
    }

    private function getUserMock(): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->method('getId')
            ->willReturn(1);

        return $stub;
    }
}

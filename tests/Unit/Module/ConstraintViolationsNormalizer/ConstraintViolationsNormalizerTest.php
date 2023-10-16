<?php

namespace Tests\Unit\Module\ConstraintViolationsNormalizer;

use App\Module\ConstraintViolationsNormalizer\ConstraintViolationsNormalizer;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Tests\Unit\TestCase;

class ConstraintViolationsNormalizerTest extends TestCase
{
    public function testNormalizeCollection(): void
    {
        $firstViolationMessage = 'Для голосования ваш рейтинг должен быть выше 5';
        $secondViolationMessage = 'Вы не можете голосовать за собственный комментарий';

        $violations = new ConstraintViolationList([
            $this->createConstraintViolationWithMessage($firstViolationMessage),
            $this->createConstraintViolationWithMessage($secondViolationMessage),
        ]);

        $constraintViolationsNormalizer = new ConstraintViolationsNormalizer();
        $normalizedViolations = $constraintViolationsNormalizer->normalizeCollection($violations);

        $expectedNormalizedViolations = [
            $firstViolationMessage,
            $secondViolationMessage,
        ];

        $this->assertEquals($expectedNormalizedViolations, $normalizedViolations);
    }

    private function createConstraintViolationWithMessage(string $message): ConstraintViolationInterface
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);

        $violation->method('getMessage')
            ->willReturn($message);

        return $violation;
    }
}

<?php

namespace Tests\Unit\Logger\MonologProcessor;


use App\Logger\MonologProcessor\ValidationFailedProcessor;
use League\Tactician\Bundle\Middleware\InvalidCommandException;
use Tests\Unit\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

final class ValidationFailedProcessorTest extends TestCase
{
    /** @var ValidationFailedProcessor  */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new ValidationFailedProcessor();
    }

    protected function tearDown(): void
    {
        unset($this->processor);

        parent::tearDown();
    }

    public function testRecordWithoutExceptionMustNotBeDescribed(): void
    {
        $record = $this->processor->processRecord([]);

        $this->assertArrayNotHasKey('extra', $record);
    }

    public function testNotInvalidCommandExceptionMustNotBeDescribed(): void
    {
        $record = $this->processor->processRecord(['exception' => new \Exception()]);

        $this->assertArrayNotHasKey('extra', $record);
    }

    public function testInvalidCommandExceptionMustBeDescribedInExtra(): void
    {
        $firstViolation = $this->createViolation('firstProperty', 'first violation message');
        $secondViolation = $this->createViolation('secondProperty', 'second violation message');
        $violations = new ConstraintViolationList([$firstViolation, $secondViolation]);

        $record = $this->processor->processRecord([
            'context' => [
                'exception' => InvalidCommandException::onCommand(new \stdClass(), $violations),
            ],
        ]);

        $this->assertArrayHasKey('extra', $record);
        $this->assertArrayHasKey('Violations', $record['extra']);

        $describedViolations = $record['extra']['Violations'];

        foreach ($violations as $expectedViolation) {
            $this->assertStringContainsString($expectedViolation->getPropertyPath(), $describedViolations);
            $this->assertStringContainsString($expectedViolation->getMessage(), $describedViolations);
        }
    }

    private function createViolation(string $propertyPath, string $violationMessage): ConstraintViolationInterface
    {
        $stub = $this->createMock(ConstraintViolation::class);
        $stub
            ->method('getPropertyPath')
            ->willReturn($propertyPath);

        $stub
            ->method('getMessage')
            ->willReturn($violationMessage);

        $stub
            ->method('__toString')
            ->willReturn($violationMessage);

        return $stub;
    }
}

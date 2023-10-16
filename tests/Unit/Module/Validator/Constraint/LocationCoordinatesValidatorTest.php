<?php

namespace Tests\Unit\Module\Validator\Constraint;

use App\Module\Validator\Constraint\LocationCoordinates;
use App\Module\Validator\Constraint\LocationCoordinatesValidator;
use Generator;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class LocationCoordinatesValidatorTest extends TestCase
{
    private const COORDINATES_SEPARATOR = ',';

    private ValidatorExecutionContextMock $executionContext;
    private LocationCoordinates $constraint;
    private LocationCoordinatesValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new LocationCoordinates();
        $this->constraint->separator = self::COORDINATES_SEPARATOR;

        $this->executionContext = new ValidatorExecutionContextMock();
        $this->validator = new LocationCoordinatesValidator();

        $this->validator->initialize($this->executionContext);
    }

    protected function tearDown(): void
    {
        unset(
            $this->validator,
            $this->constraint,
            $this->executionContext
        );

        parent::tearDown();
    }

    public function testConstraintMustBeRightInstance(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate('coordinates', $this->createMock(Constraint::class));
    }

    public function testCoordinatesIsValid(): void
    {
        $this->validator->validate(
            sprintf('%f%s%f', 89.999999, self::COORDINATES_SEPARATOR, 179.999999),
            $this->constraint
        );

        $this->assertFalse($this->executionContext->hasViolations());
    }

    /**
     * @dataProvider getInvalidCoordinates
     */
    public function testCoordinatesIsInvalid(string $coordinates): void
    {
        $this->validator->validate($coordinates, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function getInvalidCoordinates(): Generator
    {
        yield 'Coordinates without separator' => [sprintf('%f%f', 89.999999, 179.999999)];

        yield 'Coordinates with too many values' => [
            sprintf(
                '%f%s%f%s%f',
                89.999999,
                self::COORDINATES_SEPARATOR,
                99.999999,
                self::COORDINATES_SEPARATOR,
                179.999999,
            ),
        ];

        yield 'Coordinates with wrong separator' => [sprintf('%f%s%f', 79.999999, '$', 179.999999)];
    }
}

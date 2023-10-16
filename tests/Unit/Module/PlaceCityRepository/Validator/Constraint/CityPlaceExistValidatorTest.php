<?php

namespace Tests\Unit\Module\PlaceCityRepository\Validator\Constraint;

use App\Module\PlaceCityRepository\PlaceCity;
use App\Module\PlaceCityRepository\PlaceCityRepositoryInterface;
use App\Module\PlaceCityRepository\Validator\Constraint\CityPlaceExist;
use App\Module\PlaceCityRepository\Validator\Constraint\CityPlaceExistValidator;
use App\Domain\User\Validator\Constraint\UserExist;
use App\Util\Coordinates\Coordinates;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class CityPlaceExistValidatorTest extends TestCase
{
    private const CITY_COUNTRY = 'Россия';
    private const CITY_NAME = 'Новосибирск';

    /** @var CityPlaceExistValidator  */
    private $cityPlaceExistValidator;
    /** @var CityPlaceExist */
    private $constraint;
    /** @var ValidatorExecutionContextMock */
    private $executionContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cityPlaceExistValidator = new CityPlaceExistValidator($this->createMock(PlaceCityRepositoryInterface::class));
        $this->constraint = new CityPlaceExist();
        $this->executionContext = new ValidatorExecutionContextMock();
    }

    protected function tearDown(): void
    {
        unset(
            $this->cityPlaceExistValidator,
            $this->constraint,
            $this->executionContext
        );

        parent::tearDown();
    }

    public function testConstraintMustBeRightInstance(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->cityPlaceExistValidator->validate(self::CITY_NAME, new UserExist());
    }

    public function testCityNameMustBeString(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->cityPlaceExistValidator->validate(1234, $this->constraint);
    }

    public function testNoCityName(): void
    {
        $this->cityPlaceExistValidator->initialize($this->executionContext);

        $this->cityPlaceExistValidator->validate('', $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testWithoutPlaceCity(): void
    {
        $cityPlaceExistValidator = new CityPlaceExistValidator($this->getPlaceCityRepositoryMock());
        $cityPlaceExistValidator->initialize($this->executionContext);

        $cityPlaceExistValidator->validate(self::CITY_NAME, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testWithPlaceCity(): void
    {
        $placeCity = new PlaceCity(self::CITY_COUNTRY, self::CITY_NAME, new Coordinates(10, 20));
        $cityPlaceExistValidator = new CityPlaceExistValidator($this->getPlaceCityRepositoryMock($placeCity));
        $cityPlaceExistValidator->initialize($this->executionContext);

        $cityPlaceExistValidator->validate(self::CITY_NAME, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testExistencePropertyPath(): void
    {
        $this->constraint->propertyPath = 'data.cityName';

        $this->cityPlaceExistValidator->initialize($this->executionContext);

        $this->cityPlaceExistValidator->validate('', $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    private function getPlaceCityRepositoryMock(?PlaceCity $placeCity = null): PlaceCityRepositoryInterface
    {
        $stub = $this->createMock(PlaceCityRepositoryInterface::class);
        $stub
            ->method('findCity')
            ->willReturn($placeCity);

        return $stub;
    }
}

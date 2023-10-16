<?php

namespace Tests\Unit\Doctrine\Constraint\ThroughTransferObject;

use App\Doctrine\Constraint\ThroughTransferObject\ConstraintConfigValidator;
use App\Doctrine\Constraint\ThroughTransferObject\InvalidConstraintConfigException;
use App\Doctrine\Constraint\ThroughTransferObject\UniqueEntity;
use InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\TestCase;

/**
 * @group constraint
 */
class ConstraintConfigValidatorTest extends TestCase
{
    private object $transferObject;
    private ConstraintConfigValidator $configValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transferObject = $this->createTransferObject();
        $this->configValidator = new ConstraintConfigValidator(new PropertyAccessor());
    }

    protected function tearDown(): void
    {
        unset(
            $this->transferObject,
            $this->configValidator,
        );

        parent::tearDown();
    }

    public function testUnsupportedConstraint(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance of App\Doctrine\Constraint\ThroughTransferObject\UniqueEntity');

        $this->configValidator->validate($this->transferObject, $this->createMock(Constraint::class));
    }

    public function testInvalidConfigCausedOfFieldMapDoesntExist(): void
    {
        $config = [];
        $expectExceptionMessage = '"$fieldMap" is required field';
        $constraint = new UniqueEntity($config);

        $this->expectException(InvalidConstraintConfigException::class);
        $this->expectExceptionMessage($expectExceptionMessage);

        $this->configValidator->validate($this->transferObject, $constraint);
    }

    public function testInvalidConfigCausedOfInvalidEntityClass(): void
    {
        $config =  [
            'fieldMap' => [
                'transferObjectField' => 'entityField',
            ],
            'entityClass' => '\SomeNonexistentClass',
        ];

        $expectExceptionMessage = '"$entityClass" is incorrectly populated';
        $constraint = new UniqueEntity($config);

        $this->expectException(InvalidConstraintConfigException::class);
        $this->expectExceptionMessage($expectExceptionMessage);

        $this->configValidator->validate($this->transferObject, $constraint);
    }

    public function testInvalidConfigCausedOfFieldNotFoundInTransferObject(): void
    {
        $config =  [
            'fieldMap' => [
                'invalidFieldName' => 'entityField',
            ],
            'entityClass' => get_class($this->createEntity()),
        ];

        $expectExceptionMessage = '"invalidFieldName" not found in transfer object';
        $constraint = new UniqueEntity($config);

        $this->expectException(InvalidConstraintConfigException::class);
        $this->expectExceptionMessage($expectExceptionMessage);

        $this->configValidator->validate($this->transferObject, $constraint);
    }

    public function testInvalidConfigCausedOfFieldNotFoundInEntity(): void
    {
        $config =  [
            'fieldMap' => [
                'transferObjectField' => 'invalidFieldName',
            ],
            'entityClass' => get_class($this->createEntity()),
        ];

        $expectExceptionMessage = '"invalidFieldName" not found in entity';
        $constraint = new UniqueEntity($config);

        $this->expectException(InvalidConstraintConfigException::class);
        $this->expectExceptionMessage($expectExceptionMessage);

        $this->configValidator->validate($this->transferObject, $constraint);
    }

    public function testInvalidConfigCausedOfInvalidSourceEntityValue(): void
    {
        $config =  [
            'fieldMap' => [
                'transferObjectField' => 'entityField',
            ],
            'entityClass' => get_class($this->createEntity()),
            'sourceEntity' => 'invalidFieldName',
        ];

        $expectExceptionMessage = '"invalidFieldName" not found in transfer object';
        $constraint = new UniqueEntity($config);

        $this->expectException(InvalidConstraintConfigException::class);
        $this->expectExceptionMessage($expectExceptionMessage);

        $this->configValidator->validate($this->transferObject, $constraint);
    }

    public function testInvalidConfigCausedOfWrongEntityValueType(): void
    {
        $config =  [
            'fieldMap' => [
                'transferObjectField' => 'entityField',
            ],
            'entityClass' => get_class($this->createEntity()),
            'sourceEntity' => 'transferObjectField',
        ];

        $expectExceptionMessage = 'Source entity must be instance of $entityClass';
        $constraint = new UniqueEntity($config);

        $this->expectException(InvalidConstraintConfigException::class);
        $this->expectExceptionMessage($expectExceptionMessage);

        $this->configValidator->validate($this->transferObject, $constraint);
    }

    private function createTransferObject(): object
    {
        return new class() {
            public $transferObjectField;
        };
    }

    private function createEntity(): object
    {
        return new class() {
            public $entityField;
        };
    }
}

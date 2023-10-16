<?php

namespace Tests\Unit\Doctrine\Constraint\ThroughTransferObject;

use App\Doctrine\Constraint\ThroughTransferObject\ConstraintConfigValidator;
use App\Doctrine\Constraint\ThroughTransferObject\DuplicateFinder;
use App\Doctrine\Constraint\ThroughTransferObject\UniqueEntity;
use App\Doctrine\Constraint\ThroughTransferObject\UniqueEntityValidator;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Tests\Unit\TestCase;

/**
 * @group constraint
 */
class UniqueEntityValidatorTest extends TestCase
{
    private $propertyAccessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->propertyAccessor = new PropertyAccessor();
    }

    public function testInvalidConfig()
    {
        $entity = (object) [
            'entityField' => 'entityField value',
        ];
        $transferObject = (object) [
            'transferObjectField' => 'transferObjectField value',
            'source' => $entity,
        ];

        $constraint = new UniqueEntity([
            'fieldMap' => [
                'transferObjectField' => 'entityField',
            ],
            'repositoryMethod' => 'repositoryMethodFindMethod',
            'sourceEntity' => 'source',
            'entityClass' => get_class($entity),
            'message' => 'Error message',
            'errorPath' => 'error.path',
        ]);

        $constraintConfigValidator = $this->createConstraintConfigValidator($transferObject, $constraint);
        $duplicateFinder = $this->createDuplicateFinder(
            get_class($entity),
            'repositoryMethodFindMethod',
            $entity,
            [
                'entityField' => 'transferObjectField value',
            ],
            [
                $this,
            ]
        );

        $context = $this->createContext(
            'Error message',
            'error.path',
            [
                '{transferObjectField}' => 'transferObjectField value',
            ],
            'transferObjectField value'
        );
        $validator = new UniqueEntityValidator($constraintConfigValidator, $duplicateFinder, $this->propertyAccessor);
        $validator->initialize($context);
        $validator->validate($transferObject, $constraint);
    }

    private function createConstraintConfigValidator($expectedTransferObject, Constraint $expectedConstraint)
    {
        $stub = $this->createMock(ConstraintConfigValidator::class);
        $stub
            ->expects($this->once())
            ->method('validate')
            ->with($expectedTransferObject, $expectedConstraint)
        ;

        return $stub;
    }

    private function createDuplicateFinder(string $expectedEntityClass, string $expectedRepositoryMethod, $expectedSourceEntity, array $expectedCriteria, array $result): DuplicateFinder
    {
        $stub = $this->createMock(DuplicateFinder::class);
        $stub
            ->expects($this->once())
            ->method('createWithConfiguration')
            ->with($expectedEntityClass, $expectedRepositoryMethod, $expectedSourceEntity)
            ->willReturn($stub)
        ;

        $stub
            ->expects($this->once())
            ->method('findDuplicateByCriteria')
            ->with($expectedCriteria)
            ->willReturn($result)
        ;

        return $stub;
    }


    private function createContext(string $expectedMessage, string $expectedPath, array $expectedParameters, string $expectedValue): ExecutionContextInterface
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder
            ->method('atPath')
            ->with($expectedPath)
            ->willReturn($violationBuilder)
        ;
        $violationBuilder
            ->method('setParameters')
            ->with($expectedParameters)
            ->willReturn($violationBuilder)
        ;
        $violationBuilder
            ->method('setInvalidValue')
            ->with($expectedValue)
            ->willReturn($violationBuilder)
        ;
        $violationBuilder
            ->method('setCode')
            ->with(UniqueEntity::NOT_UNIQUE_ERROR)
            ->willReturn($violationBuilder)
        ;
        $violationBuilder
            ->method('addViolation')
        ;

        $stub = $this->createMock(ExecutionContextInterface::class);
        $stub
            ->method('buildViolation')
            ->with($expectedMessage)
            ->willReturn($violationBuilder)
        ;

        return $stub;
    }
}

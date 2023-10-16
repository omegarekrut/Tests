<?php

namespace Tests\Unit\Doctrine\Constraint\EntityExists;

use App\Doctrine\Constraint\EntityExists\EntityExists;
use App\Doctrine\Constraint\EntityExists\EntityExistsValidator;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Tests\Unit\TestCase;

/**
 * @group constraint
 */
class EntityExistsValidatorTest extends TestCase
{
    private const EXPECTED_IDENTIFIER = 'identifier';
    private const EXPECTED_ERROR_MESSAGE = 'entity not found';
    private const EXPECTED_ENTITY_CLASS = 'EntityClass';

    public function testIsValid(): void
    {
        $repository = $this->createRepository(self::EXPECTED_IDENTIFIER, true);
        $objectManager = $this->createObjectManager(self::EXPECTED_ENTITY_CLASS, $repository);
        $validator = new EntityExistsValidator($objectManager);

        $constraint = new EntityExists();
        $constraint->message = self::EXPECTED_ERROR_MESSAGE;
        $constraint->entityClass = self::EXPECTED_ENTITY_CLASS;

        $validator->initialize($this->createExecutionContext(false));
        $validator->validate(self::EXPECTED_IDENTIFIER, $constraint);
    }

    public function testIsNotValid(): void
    {
        $repository = $this->createRepository(self::EXPECTED_IDENTIFIER, false);
        $objectManager = $this->createObjectManager(self::EXPECTED_ENTITY_CLASS, $repository);
        $validator = new EntityExistsValidator($objectManager);

        $constraint = new EntityExists();
        $constraint->message = self::EXPECTED_ERROR_MESSAGE;
        $constraint->entityClass = self::EXPECTED_ENTITY_CLASS;

        $validator->initialize($this->createExecutionContext(true, self::EXPECTED_ERROR_MESSAGE));
        $validator->validate(self::EXPECTED_IDENTIFIER, $constraint);
    }

    public function testSkipEmptyValue(): void
    {
        $repository = $this->createRepository(self::EXPECTED_IDENTIFIER, false);
        $objectManager = $this->createObjectManager(self::EXPECTED_ENTITY_CLASS, $repository);
        $validator = new EntityExistsValidator($objectManager);

        $constraint = new EntityExists();
        $constraint->message = self::EXPECTED_ERROR_MESSAGE;
        $constraint->entityClass = self::EXPECTED_ENTITY_CLASS;
        $constraint->skipEmptyValue = true;

        $validator->initialize($this->createExecutionContext(false));
        $validator->validate('', $constraint);
    }

    public function testGetErrorForEmptyValue(): void
    {
        $repository = $this->createRepository(self::EXPECTED_IDENTIFIER, false);
        $objectManager = $this->createObjectManager(self::EXPECTED_ENTITY_CLASS, $repository);
        $validator = new EntityExistsValidator($objectManager);

        $constraint = new EntityExists();
        $constraint->message = self::EXPECTED_ERROR_MESSAGE;
        $constraint->entityClass = self::EXPECTED_ENTITY_CLASS;
        $constraint->skipEmptyValue = false;

        $validator->initialize($this->createExecutionContext(true, self::EXPECTED_ERROR_MESSAGE));
        $validator->validate('', $constraint);
    }

    public function testInvalidConstraintByWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $constraint = $this->createMock(Constraint::class);

        $validator = new EntityExistsValidator($this->createMock(ObjectManager::class));

        $validator->validate(null, $constraint);
    }

    public function testInvalidConstraintByWrongEntity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $constraint = new EntityExists();

        $validator = new EntityExistsValidator($this->createMock(ObjectManager::class));

        $validator->validate(null, $constraint);
    }

    private function createObjectManager(string $entityClass, EntityRepository $repository): ObjectManager
    {
        $metaDataFactory = $this->createMock(ClassMetadataFactory::class);
        $metaDataFactory
            ->method('isTransient')
            ->with($entityClass)
            ->willReturn(true);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager
            ->method('getRepository')
            ->with($entityClass)
            ->willReturn($repository);
        $objectManager
            ->method('getMetadataFactory')
            ->willReturn($metaDataFactory);

        return $objectManager;
    }

    private function createRepository(string $expectedIdentifier, bool $willFind): EntityRepository
    {
        $stub = $this->createMock(EntityRepository::class);
        $stub
            ->method('find')
            ->with($expectedIdentifier)
            ->willReturn($willFind ? $this : null);

        return $stub;
    }

    private function createExecutionContext(bool $called, ?string $message = null): ExecutionContextInterface
    {
        $stub = $this->createMock(ExecutionContextInterface::class);
        $stub
            ->expects($called ? $this->once() : $this->never())
            ->method('addViolation')
            ->with($message)
            ->willReturn($stub);

        return $stub;
    }
}

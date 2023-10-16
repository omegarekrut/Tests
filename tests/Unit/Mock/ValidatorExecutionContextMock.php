<?php

namespace Tests\Unit\Mock;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\MetadataInterface;

class ValidatorExecutionContextMock implements ExecutionContextInterface
{
    private $violationMessages = [];
    private $hasViolations = false;
    private $violationBuilder;
    private $object;

    public function __construct()
    {
        $this->violationBuilder = new ViolationBuilderMock();
    }

    public function hasViolations(): bool
    {
        return $this->hasViolations;
    }

    public function getViolationMessages(): array
    {
        return $this->violationMessages;
    }

    public function addViolation($message, array $params = array())
    {
        $this->hasViolations = true;
        $this->violationMessages[] = $message;
    }

    public function getGroup()
    {
        // TODO: Implement getGroup() method.
    }

    public function buildViolation($message, array $parameters = array())
    {
        $this->hasViolations = true;
        $this->violationMessages[] = $message;

        return $this->violationBuilder;
    }

    public function setObject($object): void
    {
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getClassName()
    {
        // TODO: Implement getClassName() method.
    }

    public function getMetadata()
    {
        // TODO: Implement getMetadata() method.
    }

    public function getPropertyName()
    {
        // TODO: Implement getPropertyName() method.
    }

    public function getPropertyPath($subPath = '')
    {
        // TODO: Implement getPropertyPath() method.
    }

    public function getRoot()
    {
        // TODO: Implement getRoot() method.
    }

    public function getValidator()
    {
        // TODO: Implement getValidator() method.
    }

    public function getValue()
    {
        // TODO: Implement getValue() method.
    }

    public function getViolations()
    {
        // TODO: Implement getViolations() method.
    }

    public function isConstraintValidated($cacheKey, $constraintHash)
    {
        // TODO: Implement isConstraintValidated() method.
    }

    public function isGroupValidated($cacheKey, $groupHash)
    {
        // TODO: Implement isGroupValidated() method.
    }

    public function isObjectInitialized($cacheKey)
    {
        // TODO: Implement isObjectInitialized() method.
    }

    public function markConstraintAsValidated($cacheKey, $constraintHash)
    {
        // TODO: Implement markConstraintAsValidated() method.
    }

    public function markGroupAsValidated($cacheKey, $groupHash)
    {
        // TODO: Implement markGroupAsValidated() method.
    }

    public function markObjectAsInitialized($cacheKey)
    {
        // TODO: Implement markObjectAsInitialized() method.
    }

    public function setConstraint(Constraint $constraint)
    {
        // TODO: Implement setConstraint() method.
    }

    public function setGroup($group)
    {
        // TODO: Implement setGroup() method.
    }

    public function setNode($value, $object, MetadataInterface $metadata = null, $propertyPath)
    {
        // TODO: Implement setNode() method.
    }
}

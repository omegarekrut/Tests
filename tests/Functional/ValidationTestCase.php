<?php

namespace Tests\Functional;

use Liip\FunctionalTestBundle\Validator\DataCollectingValidator;
use ReflectionClass;
use ReflectionProperty;

abstract class ValidationTestCase extends TestCase
{
    private $validator;

    protected function assertFieldInvalid(string $property, string $expectedErrorMessage): void
    {
        $validationErrors = $this->getValidator()->getLastErrors();
        $actualErrorMessage = [];

        foreach ($validationErrors as $error) {
            if ($error->getPropertyPath() === $property) {
                $actualErrorMessage[] = $error->getMessage();
            }
        }

        $this->assertContains(
            $expectedErrorMessage,
            $actualErrorMessage,
            sprintf('Searching the error message after validation for the "%s" in "%s"', $property, implode('; ', $actualErrorMessage))
        );
    }

    /**
     * @param mixed $command
     * @param string[] $properties
     * @param mixed $value
     */
    protected function assertOnlyFieldsAreInvalid($command, array $properties, $value, string $message): void
    {
        $commandReflection = new ReflectionClass(get_class($command));
        foreach ($commandReflection->getProperties(ReflectionProperty::IS_PUBLIC) as $field) {
            preg_match_all('#(@[a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#', $field->getDocComment(), $matches, PREG_PATTERN_ORDER);

            if (!in_array($field->name, $properties) || array_search('@Assert\Valid', $matches[0]) !== false) {
                continue;
            }

            $field->setValue($command, $value);
        }

        $this->getValidator()->validate($command);

        $validationErrors = $this->getValidator()->getLastErrors();
        $fieldsWithError = [];
        foreach ($validationErrors as $error) {
            if ($message !== $error->getMessage()) {
                continue;
            }

            $fieldsWithError[] = $error->getPropertyPath();
        }

        $notWaitedValidationFields = array_diff($fieldsWithError, $properties);
        $this->assertEmpty(
            $notWaitedValidationFields,
            sprintf('The field(s) %s should not get the error message "%s"', implode(', ', $notWaitedValidationFields), $message)
        );

        foreach ($properties as $field) {
            $this->assertContains(
                $field,
                $fieldsWithError,
                sprintf('The field %s has not the error message "%s" after validation', $field, $message)
            );
        }
    }

    protected function getValidator(): DataCollectingValidator
    {
        if ($this->validator instanceof DataCollectingValidator) {
            return $this->validator;
        }

        return $this->getContainer()->get('validator');
    }
}

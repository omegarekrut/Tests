<?php

namespace Tests\Functional;

use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use Symfony\Component\Form\FormInterface;

abstract class FormTestCase extends TestCase
{
    protected function createFormByEntity(string $type, $data, array $options = []): FormInterface
    {
        if (!is_object($data)) {
            throw new \InvalidArgumentException(sprintf('%s:createFormByEntity 2-м параметром ожидается объект сущности', self::class));
        }

        return $this->createFormWithData($type, $data, $options);
    }

    protected function createFormByArray(string $type, array $data, array $options = []): FormInterface
    {
        return $this->createFormWithData($type, $data, $options);
    }

    /**
     * @deprecated use ValidationTestCase
     */
    protected function assertFormSynchronizedAndValid(FormInterface $form): void
    {
        $this->assertTrue($form->isSynchronized(), 'Проверка синхронизации формы');

        if (!$form->isValid()) {
            $errors = ((string) $form->getErrors(true, false));
            $comparisonFailure = new ComparisonFailure(true, false, '', $errors);

            throw new ExpectationFailedException('Ожидалось что форма будет валидной', $comparisonFailure);
        }
    }

    protected function assertFormIsFilled(array $expectedData = [], FormInterface $form): void
    {
        $view = $form->createView();
        $children = $view->children;

        foreach ($expectedData as $key => $expectedValue) {
            $this->assertArrayHasKey($key, $children, sprintf('Проверка существования поля %s в форме', $key));
            $this->assertEquals(
                $expectedValue,
                $children[$key]->vars['value'],
                sprintf('Проверка соответствия значения поля %s в форме', $key)
            );
        }
    }

    /**
     * @deprecated use ValidationTestCase
     */
    protected function assertFormFieldInvalid(string $formField, string $errorMessage, FormInterface $form): void
    {
        $child = $this->findFormChildByName($formField, $form);
        $error = (string) $child->getErrors(false);

        $this->assertStringContainsString(
            $errorMessage,
            $error,
            sprintf('Поиск указанной ошибки в списке ошибок формы для поля %s', $formField)
        );
    }

    /**
     * @deprecated use ValidationTestCase
     */
    protected function assertFormFieldsInvalid(array $fields, string $errorMessage, FormInterface $form): void
    {
        foreach ($fields as $field) {
            $this->assertFormFieldInvalid($field, $errorMessage, $form);
        }
    }

    private function createFormWithData(string $type, $data = null, array $options = []): FormInterface
    {
        return $this->getContainer()->get('form.factory')->create($type, $data, array_merge($options, [
            'csrf_protection' => false,
        ]));
    }

    private function findFormChildByName($formName, FormInterface $form): ?FormInterface
    {
        if ($form->getName() === $formName) {
            return $form;
        }

        if ($form->has($formName)) {
            return $form->get($formName);
        }

        if (count($form)) {
            foreach ($form as $child) {
                $found = $this->findFormChildByName($formName, $child);

                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}

<?php

namespace Tests\Unit\Form;

use App\Form\XmlHttpFormValidator;
use RuntimeException;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Unit\TestCase;

/**
 * @group form
 */
class XmlHttpFormValidatorTest extends TestCase
{
    /**
     * @dataProvider getRequests
     */
    public function testRequest(bool $isSubmittedForm, bool $isXmlHttpRequest, bool $isValidationRequest): void
    {
        $form = $this->createForm($isSubmittedForm);
        $request = $this->createRequest($isXmlHttpRequest);

        $validator = new XmlHttpFormValidator($this->createSerializer());

        $this->assertEquals($isValidationRequest, $validator->isValidationRequest($form, $request));
    }

    public function getRequests(): \Generator
    {
        yield [
            true,
            true,
            true,
        ];

        yield [
            false,
            true,
            false,
        ];

        yield [
            true,
            false,
            false,
        ];

        yield [
            false,
            false,
            false,
        ];
    }

    public function testValidation(): void
    {
        $expectedErrors = $this->createMock(FormErrorIterator::class);
        $expectedSerializedErrors = serialize($expectedErrors);

        $validator = new XmlHttpFormValidator($this->createSerializer($expectedErrors, $expectedSerializedErrors));

        $form = $this->createForm(true, $expectedErrors);
        $request = $this->createRequest(true);
        $response = $validator->createValidationResponse($form, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($expectedSerializedErrors, $response->getContent());
    }

    public function testMisuseOfService(): void
    {
        $form = $this->createForm(false);
        $request = $this->createRequest(false);
        $validator = new XmlHttpFormValidator($this->createSerializer());

        $this->expectException(RuntimeException::class);

        $validator->createValidationResponse($form, $request);
    }

    private function createRequest(bool $isXmlHttpRequest): Request
    {
        $stub = $this->createMock(Request::class);
        $stub
            ->method('isXmlHttpRequest')
            ->willReturn($isXmlHttpRequest);

        return $stub;
    }

    private function createForm(bool $isSubmitted, ?FormErrorIterator $errors = null): FormInterface
    {
        $stub = $this->createMock(FormInterface::class);
        $stub
            ->method('isSubmitted')
            ->willReturn($isSubmitted);

        $stub
            ->method('getErrors')
            ->with(true)
            ->willReturn($errors);

        return $stub;
    }

    private function createSerializer($data = null, $serializedData = null): SerializerInterface
    {
        $stub = $this->createMock(SerializerInterface::class);
        $stub
            ->method('serialize')
            ->with($data, JsonEncoder::FORMAT)
            ->willReturn($serializedData);

        return $stub;
    }
}

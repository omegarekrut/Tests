<?php

namespace Tests\Functional\Util\ImageStorage\TransferObject;

use App\Util\ImageStorage\TransferObject\CroppableImageTransferObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Functional\ValidationTestCase;

class CroppableImageTransferObjectValidationTest extends ValidationTestCase
{
    private CroppableImageTransferObject $croppableImage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->croppableImage = new CroppableImageTransferObject();
    }

    protected function tearDown(): void
    {
        unset($this->croppableImage);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->getValidator()->validate($this->croppableImage);

        foreach (['imageFile', 'rotationAngle', 'sourceImageWidth', 'croppingParameters'] as $requiredProperty) {
            $this->assertFieldInvalid($requiredProperty, 'Значение не должно быть пустым.');
        }
    }

    public function testRotationAngleCannotBeLessThanZero(): void
    {
        $this->croppableImage->rotationAngle = -1;

        $this->getValidator()->validate($this->croppableImage);

        $this->assertFieldInvalid('rotationAngle', 'Значение должно быть 0 или больше.');
    }

    public function testRotationAngleCannotBeGreaterThan360(): void
    {
        $this->croppableImage->rotationAngle = 361;

        $this->getValidator()->validate($this->croppableImage);

        $this->assertFieldInvalid('rotationAngle', 'Значение должно быть 360 или меньше.');
    }

    public function testSourceImageWidthMustBeGreaterThanZero(): void
    {
        $this->croppableImage->sourceImageWidth = 0;

        $this->getValidator()->validate($this->croppableImage);

        $this->assertFieldInvalid('sourceImageWidth', 'Значение должно быть больше чем "0".');
    }

    public function testCroppingParametersMustContainsAllRequiredParameters(): void
    {
        $this->croppableImage->croppingParameters = [];

        $this->getValidator()->validate($this->croppableImage);

        $this->assertFieldInvalid('croppingParameters[x0]', 'Это поле отсутствует.');
        $this->assertFieldInvalid('croppingParameters[y0]', 'Это поле отсутствует.');
        $this->assertFieldInvalid('croppingParameters[x1]', 'Это поле отсутствует.');
        $this->assertFieldInvalid('croppingParameters[y1]', 'Это поле отсутствует.');
    }

    public function testCroppingParametersMustNotBeBlank(): void
    {
        $this->croppableImage->croppingParameters = [
            'x0' => null,
            'y0' => null,
            'x1' => null,
            'y1' => null,
        ];

        $this->getValidator()->validate($this->croppableImage);

        $this->assertFieldInvalid('croppingParameters[x0]', 'Значение не должно быть пустым.');
        $this->assertFieldInvalid('croppingParameters[y0]', 'Значение не должно быть пустым.');
        $this->assertFieldInvalid('croppingParameters[x1]', 'Значение не должно быть пустым.');
        $this->assertFieldInvalid('croppingParameters[y1]', 'Значение не должно быть пустым.');
    }

    public function testValidationShouldBePassedForCorrectFilledObject(): void
    {
        $this->croppableImage->imageFile = new UploadedFile(
            sprintf('%s/image20x29.jpeg', $this->getDataFixturesFolder()),
            'image20x29.jpeg',
            null,
            100,
            0,
            true
        );
        $this->croppableImage->sourceImageWidth = 20;
        $this->croppableImage->rotationAngle = 90;
        $this->croppableImage->croppingParameters = [
            'x0' => 0,
            'y0' => 0,
            'x1' => 10,
            'y1' => 10,
        ];

        $this->getValidator()->validate($this->croppableImage);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}

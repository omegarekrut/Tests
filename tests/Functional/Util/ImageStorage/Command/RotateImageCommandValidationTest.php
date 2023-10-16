<?php

namespace Tests\Functional\Util\ImageStorage\Command;

use App\Util\ImageStorage\Command\RotateImageCommand;
use App\Util\ImageStorage\Image;
use Tests\Functional\ValidationTestCase;

class RotateImageCommandValidationTest extends ValidationTestCase
{
    private Image $image;

    protected function setUp(): void
    {
        parent::setUp();

        $this->image = new Image(sprintf('%simage20x29.jpeg', $this->getDataFixturesFolder()));
    }

    protected function tearDown(): void
    {
        unset($this->image);

        parent::tearDown();
    }

    public function testRotationAngleCannotBeLessThanZero(): void
    {
        $rotateLessThanZeroCommand = new RotateImageCommand($this->image, -1);

        $this->getValidator()->validate($rotateLessThanZeroCommand);

        $this->assertFieldInvalid('rotationAngle', 'Значение должно быть 0 или больше.');
    }

    public function testRotationAngleCannotBeGreaterThan360(): void
    {
        $rotateGreaterThan360Command = new RotateImageCommand($this->image, 361);

        $this->getValidator()->validate($rotateGreaterThan360Command);

        $this->assertFieldInvalid('rotationAngle', 'Значение должно быть 360 или меньше.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $rotationCorrectCommand = new RotateImageCommand($this->image, 90);

        $this->getValidator()->validate($rotationCorrectCommand);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}

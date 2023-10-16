<?php

namespace Tests\Functional\Util\Command;

use App\Util\ImageStorage\Command\RotateImageCommand;
use App\Util\ImageStorage\Image;
use Tests\Functional\ValidationTestCase;

class RotateImageCommandValidationTest extends ValidationTestCase
{
    public function testrotationAngleCannotBeLessThanZero(): void
    {
        $command = new RotateImageCommand(new Image('filename'), -1);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('rotationAngle', 'Значение должно быть 0 или больше.');
    }

    public function testRotationAngleCannotBeGreaterThan360(): void
    {
        $command = new RotateImageCommand(new Image('filename'), 361);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('rotationAngle', 'Значение должно быть 360 или меньше.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $command = new RotateImageCommand($this->createMock(Image::class), 180);

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}

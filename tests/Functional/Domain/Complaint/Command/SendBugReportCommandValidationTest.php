<?php

namespace Tests\Functional\Domain\Complaint\Command;

use App\Domain\Complaint\Command\SendBugReportCommand;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Functional\ValidationTestCase;

class SendBugReportCommandValidationTest extends ValidationTestCase
{
    private SendBugReportCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new SendBugReportCommand($this->createMock(User::class), 'page/url');
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testTextMustNotBeEmpty(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('text', 'Значение не должно быть пустым.');
    }

    public function testTextMustBeString(): void
    {
        $this->command->text = ['array item'];

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('text', 'Тип значения должен быть string.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->text = 'some text';

        $errors = $this->getValidator()->validate($this->command);

        $this->assertEmpty($errors);
    }

    public function testImageMustBeFile(): void
    {
        $this->command->text = 'some text';
        $this->command->image = 'some image';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('image', 'Файл не может быть найден.');
    }

    public function testImageMustBeValidType(): void
    {
        $this->command->text = 'some text';
        $this->command->image = new UploadedFile(
            sprintf('%stempSemanticLinkImport.xlsx', $this->getDataFixturesFolder()),
            'image20x29.jpeg',
            'image/jpeg',
            filesize(sprintf('%simage20x29.jpeg', $this->getDataFixturesFolder())),
            UPLOAD_ERR_OK,
            true
        );

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('image', 'Файл должен быть в формате jpeg, png или gif');
    }
}

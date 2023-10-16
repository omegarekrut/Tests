<?php

namespace Tests\Functional\Domain\SemanticLink\Command;

use App\Domain\SemanticLink\Command\ImportSemanticLinksCommand;
use App\Domain\SemanticLink\Command\UploadAndImportSemanticLinksCommand;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Functional\ValidationTestCase;

/**
 * @group semantic_link
 */
class UploadAndImportSemanticLinksCommandValidationTest extends ValidationTestCase
{
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new UploadAndImportSemanticLinksCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testInvalidMimeTypeFile(): void
    {
        $fileXlsx = 'text';

        $this->command->file = $fileXlsx;
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('file', 'Файл не может быть найден.');
    }

    public function testInvalidStructureFile(): void
    {
        $fileXlsx = new UploadedFile(
            $this->getTestFilePath('tempInvalidStructureSemanticLinkImport.xlsx'),
            'tempInvalidStructureSemanticLinkImport.xlsx',
            'application/xlsx',
            15,
            0,
            true
        );

        $this->command->file = $fileXlsx;
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('file', 'Неверная структура файла');
    }

    public function testValidStructureFile(): void
    {
        $fileXlsx = new UploadedFile(
            $this->getTestFilePath('tempSemanticLinkImport.xlsx'),
            'tempSemanticLinkImport.xlsx',
            'application/xlsx',
            15,
            0,
            true
        );

        $this->command->file = $fileXlsx;
        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    private function getTestFilePath(string $filename): string
    {
        return $this->getDataFixturesFolder().$filename;
    }
}

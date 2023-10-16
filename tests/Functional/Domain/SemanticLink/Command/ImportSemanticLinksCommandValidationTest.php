<?php

namespace Tests\Functional\Domain\SemanticLink\Command;

use App\Domain\SemanticLink\Command\ImportSemanticLinksCommand;
use Tests\Functional\ValidationTestCase;

/**
 * @group semantic_link
 */
class ImportSemanticLinksCommandValidationTest extends ValidationTestCase
{
    /** @var ImportSemanticLinksCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new ImportSemanticLinksCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFilePath(): void
    {
        $filePath = '';

        $this->command->filePath = $filePath;
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('filePath', 'Значение не должно быть пустым.');
    }

    public function testNotException(): void
    {
        $filePath = '/var/www/html/var/tmp';

        $this->command->filePath = $filePath;
        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}

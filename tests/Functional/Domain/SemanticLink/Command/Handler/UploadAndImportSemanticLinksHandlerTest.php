<?php

namespace Tests\Functional\Domain\SemanticLink\Command\Handler;

use App\Domain\SemanticLink\Command\Handler\UploadAndImportSemanticLinksHandler;
use App\Domain\SemanticLink\Command\UploadAndImportSemanticLinksCommand;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Functional\TestCase;

/**
 * @group semantic_link
 */
class UploadAndImportSemanticLinksHandlerTest extends TestCase
{
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $commandBus = $this->getCommandBus();

        $temporaryUploadDirectory = $this->getContainer()->getParameter('kernel.tmp_dir');

        $this->handler = new UploadAndImportSemanticLinksHandler($commandBus, $temporaryUploadDirectory);
    }

    protected function tearDown(): void
    {
        unset($this->handler);

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $command = new UploadAndImportSemanticLinksCommand();
        $command->file = $this->getFileImportSemanticLinks();

        $actualException = null;

        try {
            $this->handler->handle($command);
        } catch (\Exception $exception) {
            $actualException = $exception;
        }

        $this->assertEmpty($actualException);
    }

    private function getFileImportSemanticLinks(): UploadedFile
    {
        return new UploadedFile(
            $this->getCopyTestFilePath('tempSemanticLinkImport.xlsx'),
            'tempSemanticLinkImport.xlsx',
            'application/xlsx',
            15,
            0,
            true
        );
    }

    private function getCopyTestFilePath(string $filename): string
    {
        $this
            ->getContainer()
            ->get('filesystem')
            ->copy(
                $this->getDataFixturesFolder().$filename,
                $this->getDataFixturesFolder().'copy_'.$filename
            );

        return $this->getDataFixturesFolder().'copy_'.$filename;
    }
}

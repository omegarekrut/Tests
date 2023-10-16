<?php

namespace Tests\Functional\Domain\SemanticLink\Command\Handler;

use App\Domain\SemanticLink\Command\Handler\ImportSemanticLinksHandler;
use App\Domain\SemanticLink\Command\ImportSemanticLinksCommand;
use App\Domain\SemanticLink\Helper\SpreadsheetFactory;
use App\Domain\SemanticLink\Normalizer\SemanticLinkUrlNormalizer;
use App\Domain\SemanticLink\Repository\SemanticLinkRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Tests\Functional\TestCase;

/**
 * @group semantic_link
 */
class ImportSemanticLinksHandlerTest extends TestCase
{
    /** @var ImportSemanticLinksHandler */
    private $handler;

    /** @var SemanticLinkRepository */
    private $semanticLinkRepository;

    /** @var Filesystem */
    private $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $commandBus = $this->getContainer()->get('tactician.commandbus.default');
        $spreadsheetFactory = new SpreadsheetFactory();
        $monolog = $this->createMock(LoggerInterface::class);
        $urlNormalizer = new SemanticLinkUrlNormalizer();
        $this->filesystem = $this->getContainer()->get('filesystem');
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');

        $this->handler = new ImportSemanticLinksHandler($commandBus, $spreadsheetFactory, $urlNormalizer, $monolog, $this->filesystem, $eventDispatcher);

        $this->semanticLinkRepository = $this->getContainer()->get(SemanticLinkRepository::class);
    }

    protected function tearDown(): void
    {
        unset($this->handler, $this->semanticLinkRepository);

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $command = new ImportSemanticLinksCommand();
        $command->filePath = $this->getCopyTestFilePath('tempSemanticLinkImport.xlsx');

        $actualException = null;

        try {
            $this->handler->handle($command);
        } catch (\Exception $exception) {
            $actualException = $exception;
        }

        $this->assertEmpty($actualException);
    }

    private function getCopyTestFilePath(string $filename): string
    {
        $this->filesystem->copy($this->getDataFixturesFolder().$filename, $this->getDataFixturesFolder().'copy_'.$filename);

        return sprintf('%scopy_%s', $this->getDataFixturesFolder(), $filename);
    }
}

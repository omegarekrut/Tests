<?php

namespace Tests\Functional\Domain\Rss\Command\Handler;

use App\Domain\Rss\Command\BuildRssCommand;
use Prophecy\PhpUnit\ProphecyTrait;
use Tests\DataFixtures\ORM\Record\LoadTackles;
use Tests\Functional\RepositoryTestCase;

/**
 * @group rss
 */
class BuildRssHandlerTest extends RepositoryTestCase
{
    use ProphecyTrait;

    private $rssFeedResourceStorage;
    private $rssFeedResource;
    private $commandBus;
    private $recordRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([LoadTackles::class]);

        $this->commandBus = $this->getContainer()->get('tactician.commandbus.default');
        $this->recordRepository = $this->getContainer()->get('App\Domain\Record\Common\Repository\RecordRepository');
        $this->rssFeedResourceStorage = $this->getContainer()->get('App\Domain\Rss\RssFeed\RssFeedResourceStorage');
        $this->rssFeedResource = $this->rssFeedResourceStorage->getFeedResource();

        if (file_exists($this->rssFeedResource->getFilename())) {
            unlink($this->rssFeedResource->getFilename());
        }
    }

    protected function tearDown(): void
    {
        unset(
            $this->rssFeedResourceStorage,
            $this->rssFeedResource,
            $this->commandBus,
            $this->recordRepository
        );

        parent::tearDown();
    }

    public function testCreateNewFile(): void
    {
        $this->assertFileDoesNotExist($this->rssFeedResource->getFilename());

        $command = new BuildRssCommand($this->rssFeedResource, false);
        $status = $this->commandBus->handle($command);

        $this->assertSame(0, $status);
        $this->assertFileExists($this->rssFeedResource->getFilename());
        $this->assertNotEquals('', file_get_contents($this->rssFeedResource->getFilename()));
    }
}

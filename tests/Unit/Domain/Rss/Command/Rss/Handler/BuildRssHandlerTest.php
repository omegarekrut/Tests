<?php

namespace Tests\Unit\Domain\Rss\Command\Rss\Handler;

use App\Domain\Record\Common\Command\Rss\MarkAllowedRecordsAsPublishedInRssCommand;
use App\Domain\Record\Common\Repository\RecordRepository;
use App\Domain\Rss\Command\BuildRssCommand;
use App\Domain\Rss\Command\Handler\BuildRssHandler;
use App\Domain\Rss\Exception\NoNeedGenerateRssException;
use App\Module\Rss\RssFeedResourceInterface;
use App\Module\Rss\RssWriterInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\TestCase;
use ZenRss\Repository\ItemRepository;
use ZenRss\Repository\RepositoryManager;

/**
 * @group rss
 */
class BuildRssHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $commandBus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandBus = new CommandBusMock();
    }

    protected function tearDown(): void
    {
        unset(
            $this->commandBus
        );

        parent::tearDown();
    }

    public function testBuildRssSuccessfully(): void
    {
        $buildRssHandler = $this->getBuildRssHandler(1);
        $command = new BuildRssCommand($this->getRssFeedResourceMock(), false);
        $status = $buildRssHandler->handle($command);

        $this->assertEquals(0, $status);
    }

    public function testNoNeedBuildRssFile(): void
    {
        $this->expectException(NoNeedGenerateRssException::class);
        $this->expectExceptionMessage('Not necessary to regenerate rss file');

        $buildRssHandler = $this->getBuildRssHandler(0);
        $command = new BuildRssCommand($this->getRssFeedResourceMock(), false);
        $buildRssHandler->handle($command);
    }

    public function testForcedBuildFile(): void
    {
        $buildRssHandler = $this->getBuildRssHandler(0);
        $command = new BuildRssCommand($this->getRssFeedResourceMock(), true);
        $status = $buildRssHandler->handle($command);

        $this->assertEquals(0, $status);

        $status = $buildRssHandler->handle($command);

        $this->assertEquals(0, $status);
    }

    public function testMarkAllowedRecordsAsPublishedInRssCommandIsHandled(): void
    {
        $buildRssHandler = $this->getBuildRssHandler(1);
        $command = new BuildRssCommand($this->getRssFeedResourceMock(), false);
        $status = $buildRssHandler->handle($command);

        $this->assertEquals(0, $status);

        $allowedRecordsAreMarked = $this->commandBus->isHandled(MarkAllowedRecordsAsPublishedInRssCommand::class);

        $this->assertTrue($allowedRecordsAreMarked);
    }

    private function getRssWriterMock(): RssWriterInterface
    {
        $rssWriterMock = $this->prophesize(RssWriterInterface::class);

        return $rssWriterMock->reveal();
    }

    private function getRssRepositoryManagerMock(): RepositoryManager
    {
        $itemRepositoryMock = $this->prophesize(ItemRepository::class);

        $itemRepositoryMock
            ->getItemList()
            ->willReturn([]);

        $itemRepositoryMock = $itemRepositoryMock->reveal();

        $rssRepositoryManagerMock = $this->prophesize(RepositoryManager::class);

        $rssRepositoryManagerMock
            ->getRepository()
            ->willReturn($itemRepositoryMock);

        return $rssRepositoryManagerMock->reveal();
    }

    private function getRssRecordRepositoryMock(int $countOfAllowedForPublishInRss): RecordRepository
    {
        $rssRecordRepositoryMock = $this->prophesize(RecordRepository::class);

        $rssRecordRepositoryMock
            ->getCountAllowedForPublishInRss()
            ->willReturn($countOfAllowedForPublishInRss);

        return $rssRecordRepositoryMock->reveal();
    }

    private function getRssFeedResourceMock(): RssFeedResourceInterface
    {
        $rssFeedResourceInterface = $this->prophesize(RssFeedResourceInterface::class);

        return $rssFeedResourceInterface->reveal();
    }

    private function getBuildRssHandler(int $countOfAllowedForPublishInRss): BuildRssHandler
    {
        return new BuildRssHandler(
            $this->getRssRecordRepositoryMock($countOfAllowedForPublishInRss),
            $this->getRssWriterMock(),
            $this->commandBus,
            $this->getRssRepositoryManagerMock()
        );
    }
}

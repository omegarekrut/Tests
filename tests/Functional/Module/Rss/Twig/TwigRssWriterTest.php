<?php

namespace Tests\Functional\Module\Rss\Twig;

use App\Module\Rss\RssFeedResourceInterface;
use App\Module\Rss\Twig\TwigRssWriter;
use Tests\Functional\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @group rss
 */
class TwigRssWriterTest extends TestCase
{
    private const TEMPLATE_NAME = 'expected-template-name';
    private const EXPECTED_RSS_CONTENT = 'rss content';

    public function testWriterCantWriteRssWithTemplateToExpectedResource(): void
    {
        $feedResource = $this->createTemporaryFeedResource();
        $templateLoader = new ArrayLoader([
            self::TEMPLATE_NAME => self::EXPECTED_RSS_CONTENT,
        ]);

        $rssWriter = new TwigRssWriter(new Environment($templateLoader), self::TEMPLATE_NAME);
        $rssWriter->write([], $feedResource);

        $this->assertTrue(file_exists($feedResource->getFilename()));
        $this->assertEquals(self::EXPECTED_RSS_CONTENT, file_get_contents($feedResource->getFilename()));
    }

    private function createTemporaryFeedResource(): RssFeedResourceInterface
    {
        $stub = $this->createMock(RssFeedResourceInterface::class);
        $stub
            ->method('getFilename')
            ->willReturn(tempnam(sys_get_temp_dir(), 'feed-resource-'));

        return $stub;
    }
}

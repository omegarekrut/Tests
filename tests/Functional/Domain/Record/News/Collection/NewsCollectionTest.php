<?php

namespace Tests\Functional\Domain\Record\News\Collection;

use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\News\Collection\NewsCollection;
use App\Domain\Record\News\Repository\NewsRepository;
use Tests\DataFixtures\ORM\Record\LoadNews;
use Tests\Functional\TestCase;

class NewsCollectionTest extends TestCase
{
    private const NEWS_LIMIT = 10;

    public function testSortByCreatedAtAsc(): void
    {
        $this->loadFixtures([
            LoadNews::class,
        ]);

        $newsRepository = $this->getContainer()->get(NewsRepository::class);
        $unsortedNews = new NewsCollection($newsRepository->findBy([], [], self::NEWS_LIMIT));

        $sortedNews = $unsortedNews->sortByCreatedAtAsc();

        for ($i = 0; $i < $sortedNews->count() - 1; $i++) {
            /** @var Record $leftNews */
            $leftNews = $sortedNews->get($i);
            /** @var Record $rightNews */
            $rightNews = $sortedNews->get($i + 1);

            $this->assertTrue($leftNews->getCreatedAt() <= $rightNews->getCreatedAt());
        }
    }
}

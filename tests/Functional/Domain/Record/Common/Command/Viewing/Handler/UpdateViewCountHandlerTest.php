<?php

namespace Tests\Functional\Domain\Record\Common\Command\Viewing\Handler;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Common\Command\Viewing\UpdateViewCountCommand;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\Functional\TestCase;

class UpdateViewCountHandlerTest extends TestCase
{
    /** @var Article */
    private $article;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
        ])->getReferenceRepository();

        $this->article = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
    }

    protected function tearDown(): void
    {
        unset(
            $this->article
        );

        parent::tearDown();
    }

    public function testIncrementArticleViewCount(): void
    {
        $expectedViewCount = $this->article->getViews() + 1;

        $command = new UpdateViewCountCommand($this->article->getId());
        $this->getCommandBus()->handle($command);

        $this->assertEquals($expectedViewCount, $this->article->getViews());
    }
}

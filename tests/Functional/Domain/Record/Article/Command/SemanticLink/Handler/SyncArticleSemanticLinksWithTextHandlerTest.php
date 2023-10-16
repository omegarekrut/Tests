<?php

namespace Tests\Functional\Domain\Record\Article\Command\SemanticLink\Handler;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Article\Command\SemanticLink\SyncArticleSemanticLinksWithTextCommand;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticlesForSemanticLinks;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithEqualsUrlRelativeArticle;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\TestCase;

/**
 * @group semantic_link
 */
class SyncArticleSemanticLinksWithTextHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadArticlesForSemanticLinks::class,
            LoadSemanticLinkWithValidUri::class,
        ])->getReferenceRepository();

        $exceptedSemanticLink = $referenceRepository->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);

        /** @var Article $article */
        $article = $referenceRepository->getReference(LoadArticlesForSemanticLinks::REFERENCE_NAME);
        $this->assertEmpty($article->getRecordSemanticLinks());

        $command = new SyncArticleSemanticLinksWithTextCommand($article->getId());

        $this->getCommandBus()->handle($command);

        $this->assertNotEmpty($article->getRecordSemanticLinks());
        $this->assertEquals([$exceptedSemanticLink], $article->getRecordSemanticLinks()->getSemanticLinks()->toArray());
    }

    public function testHandleWithEqualsUrlRelativeArticle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSemanticLinkWithEqualsUrlRelativeArticle::class,
        ])->getReferenceRepository();

        /** @var Article $article */
        $article = $referenceRepository->getReference(LoadArticlesForSemanticLinks::REFERENCE_NAME);
        $this->assertEmpty($article->getRecordSemanticLinks());

        $command = new SyncArticleSemanticLinksWithTextCommand($article->getId());

        $this->getCommandBus()->handle($command);

        $this->assertEmpty($article->getRecordSemanticLinks());
    }
}

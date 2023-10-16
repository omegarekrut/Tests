<?php

namespace Tests\Functional\Domain\Record\Article\Command\SemanticLink\Handler;

use App\Domain\Record\Article\Command\SemanticLink\SyncAllArticlesSemanticLinksWithTextCommand;
use App\Domain\Record\Article\Entity\Article;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticlesForSemanticLinks;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\TestCase;

/**
 * @group semantic_link
 */
class SyncAllArticlesSemanticLinksWithTextHandlerTest extends TestCase
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

        $command = new SyncAllArticlesSemanticLinksWithTextCommand();

        $this->getCommandBus()->handle($command);

        $this->assertNotEmpty($article->getRecordSemanticLinks());
        $this->assertEquals([$exceptedSemanticLink], $article->getRecordSemanticLinks()->getSemanticLinks()->toArray());
    }
}

<?php

namespace Tests\Functional\Domain\Record\Article\Command\SemanticLink;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Article\Command\SemanticLink\SyncArticleSemanticLinksWithTextCommand;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\Functional\ValidationTestCase;

/**
 * @group semantic_link
 */
class SyncArticleSemanticLinksWithTextCommandValidationTest extends ValidationTestCase
{
    public function testArticleFound(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
        ])->getReferenceRepository();

        /** @var Article $article */
        $article = $referenceRepository->getReference(LoadArticles::getRandReferenceName());

        $command = new SyncArticleSemanticLinksWithTextCommand($article->getId());

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testArticleNotFound(): void
    {
        $command = new SyncArticleSemanticLinksWithTextCommand(0005);

        $this->getValidator()->validate($command);

        $this->assertNotEmpty($this->getValidator()->getLastErrors());
        $this->assertFieldInvalid('articleId', 'Запись не найдена.');
    }
}

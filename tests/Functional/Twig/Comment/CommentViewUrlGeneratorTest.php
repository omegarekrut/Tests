<?php

namespace Tests\Functional\Twig\Comment;

use App\Domain\Comment\Entity\Comment;
use App\Twig\Comment\CommentViewUrlGenerator;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\Functional\TestCase as FunctionalTestCase;

class CommentViewUrlGeneratorTest extends FunctionalTestCase
{
    public function testGenerateUrlForComment(): void
    {
        $commentViewUrlGenerator = $this->getContainer()->get(CommentViewUrlGenerator::class);

        $referenceRepository = $this->loadFixtures([LoadArticles::class])->getReferenceRepository();
        $article = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        /** @var Comment $comment */
        $comment = $article->getComments()->first();

        $expectedUrl = sprintf('/articles/view/%d/#comment%s', $article->getId(), $comment->getSlug());

        $actualUrl = $commentViewUrlGenerator->generate($comment);

        $this->assertEquals($expectedUrl, $actualUrl);
    }
}

<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Company\Command\RewriteCompanyAuthorNameCommand;
use App\Domain\Company\Exception\RubricsIsEmptyException;
use App\Domain\Record\Article\Entity\Article;
use Tests\DataFixtures\ORM\Comment\LoadOneCommentByCompanyAuthor;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleByCompanyAuthor;
use Tests\Functional\TestCase;

/**
 * @group company
 */
class RewriteCompanyAuthorNameHandlerTest extends TestCase
{
    /**
     * @throws RubricsIsEmptyException
     */
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadArticleByCompanyAuthor::class,
            LoadOneCommentByCompanyAuthor::class,
        ])->getReferenceRepository();

        $articleByCompanyAuthor = $referenceRepository->getReference(LoadArticleByCompanyAuthor::REFERENCE_NAME);
        assert($articleByCompanyAuthor instanceof Article);

        $commentByCompanyAuthor = $referenceRepository->getReference(LoadOneCommentByCompanyAuthor::REFERENCE_NAME);
        assert($commentByCompanyAuthor instanceof Comment);

        $company = $articleByCompanyAuthor->getCompanyAuthor();

        $expectedCompanyName = 'New company name';
        $company->editBasicInfo(
            $expectedCompanyName,
            $company->getSlug(),
            $company->getScopeActivity(),
            $company->getRubrics()
        );

        $command = new RewriteCompanyAuthorNameCommand($company->getId());

        $this->getCommandBus()->handle($command);

        $this->assertEquals($expectedCompanyName, $articleByCompanyAuthor->getCompanyAuthorName());
        $this->assertEquals($expectedCompanyName, $commentByCompanyAuthor->getCompanyAuthorName());
    }
}

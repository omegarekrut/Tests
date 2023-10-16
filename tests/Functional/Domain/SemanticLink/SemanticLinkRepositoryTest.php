<?php

namespace Tests\Functional\Domain\SemanticLink;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\SemanticLink\Entity\SemanticLink;
use App\Domain\SemanticLink\Repository\SemanticLinkRepository;
use App\Domain\SemanticLink\Search\SemanticLinkSearchData;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleWithRecordSemanticLink;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\RepositoryTestCase;

/**
 * @group semantic_link
 */
class SemanticLinkRepositoryTest extends RepositoryTestCase
{
    /** @var ReferenceRepository */
    private $referenceRepository;

    /** @var SemanticLinkRepository */
    private $semanticLinkRepository;

    /** @var SemanticLink */
    private $expectedSemanticLink;

    /** @var SemanticLinkSearchData */
    private $semanticLinkSearchData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadSemanticLinkWithValidUri::class,
        ])->getReferenceRepository();

        $this->expectedSemanticLink = $this->referenceRepository->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);
        $this->semanticLinkRepository = $this->getContainer()->get(SemanticLinkRepository::class);
        $this->semanticLinkSearchData = new SemanticLinkSearchData();
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->expectedSemanticLink,
            $this->semanticLinkRepository,
            $this->semanticLinkSearchData
        );

        parent::tearDown();
    }

    public function testSearchDataCanBeFoundByEmptySearch(): void
    {
        $expectedCount = count($this->referenceRepository->getIdentities());

        $allSemanticLinkDataQueryBuilder = $this->semanticLinkRepository->createQueryBuilderForFindAllBySearchData($this->semanticLinkSearchData);
        $result = $allSemanticLinkDataQueryBuilder->getQuery()->getResult();

        $this->assertCount($expectedCount, $result);
    }

    public function testSearchDataCanBeFoundByUri(): void
    {
        $this->semanticLinkSearchData->uri = $this->expectedSemanticLink->getUri();

        $semanticLinkDataQueryBuilder = $this->semanticLinkRepository->createQueryBuilderForFindAllBySearchData($this->semanticLinkSearchData);
        $result = $semanticLinkDataQueryBuilder->getQuery()->getResult();

        $this->assertContains($this->expectedSemanticLink, $result);
    }

    public function testSearchDataCanBeFoundByText(): void
    {
        $this->semanticLinkSearchData->text = $this->expectedSemanticLink->getText();

        $semanticLinkDataQueryBuilder = $this->semanticLinkRepository->createQueryBuilderForFindAllBySearchData($this->semanticLinkSearchData);
        $result = $semanticLinkDataQueryBuilder->getQuery()->getResult();

        $this->assertContains($this->expectedSemanticLink, $result);
    }

    public function testSearchDataCanBeFoundByNumberActiveLinks(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadArticleWithRecordSemanticLink::class,
        ])->getReferenceRepository();

        /** @var Article $article */
        $article = $referenceRepository->getReference(LoadArticleWithRecordSemanticLink::REFERENCE_NAME);

        $expectedSemanticLink = $article->getRecordSemanticLinks()[0];

        $this->semanticLinkSearchData->numberActiveLinks = $expectedSemanticLink->getSemanticLink()->getNumberActiveLinks();

        $semanticLinkDataQueryBuilder = $this->semanticLinkRepository->createQueryBuilderForFindAllBySearchData($this->semanticLinkSearchData);
        $result = $semanticLinkDataQueryBuilder->getQuery()->getResult();

        $this->assertContains($expectedSemanticLink->getSemanticLink(), $result);
    }
}

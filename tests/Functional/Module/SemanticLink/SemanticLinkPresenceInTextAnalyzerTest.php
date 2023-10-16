<?php

namespace Tests\Functional\Module\SemanticLink;

use App\Domain\SemanticLink\Entity\SemanticLink;
use App\Module\SemanticLink\SemanticLinkMatch;
use App\Module\SemanticLink\SemanticLinksPresenceInTextAnalyzer;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\SemanticLink\LoadAdvancedSemanticLinkWithValidUri;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\TestCase;

/**
 * @group semantic_link
 */
class SemanticLinkPresenceInTextAnalyzerTest extends TestCase
{
    /** @var ReferenceRepository */
    private $referenceRepository;
    /** @var SemanticLinksPresenceInTextAnalyzer */
    private $analyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures(
            [
                LoadAdvancedSemanticLinkWithValidUri::class,
                LoadSemanticLinkWithValidUri::class,
                LoadAdvancedSemanticLinkWithValidUri::class,
            ]
        )->getReferenceRepository();

        $this->analyzer = $this->getContainer()->get(SemanticLinksPresenceInTextAnalyzer::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->analyzer
        );

        parent::tearDown();
    }

    public function testExistSemanticLinksInText(): void
    {
        /** @var SemanticLink $semanticLink */
        $semanticLink = $this->referenceRepository->getReference(LoadAdvancedSemanticLinkWithValidUri::REFERENCE_NAME);

        $semanticLinkMatches = $this->analyzer->analyzeText('Lorem ipsum dolor sit amet, black hole hyper отзыв перейти consectetur adipiscing elit. Morbi convallis sagittis bibendum.');

        $this->assertContainsOnlyInstancesOf(SemanticLinkMatch::class, $semanticLinkMatches);
        $this->assertNotEmpty($semanticLinkMatches);

        $this->assertEquals($semanticLink, $semanticLinkMatches[0]->semanticLink);
        $this->assertEquals($semanticLink->getText(), $semanticLinkMatches[0]->matchedKeyword);
    }

    public function testNotExistSemanticLinksInText(): void
    {
        $semanticLinkMatches = $this->analyzer->analyzeText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi convallis sagittis bibendum.');

        $this->assertContainsOnlyInstancesOf(SemanticLinkMatch::class, $semanticLinkMatches);
        $this->assertEmpty($semanticLinkMatches);
    }
}

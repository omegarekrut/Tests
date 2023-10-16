<?php

namespace Tests\Functional\Domain\Record\CompanyArticle\Command\SemanticLink\Handler;

use App\Domain\Record\CompanyArticle\Command\SemanticLink\SyncAllCompanyArticleSemanticLinksWithTextCommand;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\SemanticLink\Entity\SemanticLink;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleForSemanticLinks;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\TestCase;

/**
 * @group semantic_link
 */
class SyncAllCompanyArticleSemanticLinksWithTextHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyArticleForSemanticLinks::class,
            LoadSemanticLinkWithValidUri::class,
        ])->getReferenceRepository();

        /** @var SemanticLink $exceptedSemanticLink */
        $exceptedSemanticLink = $referenceRepository->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);

        /** @var CompanyArticle $companyArticle */
        $companyArticle = $referenceRepository->getReference(LoadCompanyArticleForSemanticLinks::REFERENCE_NAME);

        $this->assertEmpty($companyArticle->getRecordSemanticLinks());

        $command = new SyncAllCompanyArticleSemanticLinksWithTextCommand();

        $this->getCommandBus()->handle($command);

        $this->assertNotEmpty($companyArticle->getRecordSemanticLinks());
        $this->assertEquals([$exceptedSemanticLink], $companyArticle->getRecordSemanticLinks()->getSemanticLinks()->toArray());
    }
}

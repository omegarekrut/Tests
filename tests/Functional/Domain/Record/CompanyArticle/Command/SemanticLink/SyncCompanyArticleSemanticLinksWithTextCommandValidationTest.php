<?php

namespace Tests\Functional\Domain\Record\CompanyArticle\Command\SemanticLink;

use App\Domain\Record\CompanyArticle\Command\SemanticLink\SyncCompanyArticleSemanticLinksWithTextCommand;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadAquaMotorcycleShopsCompanyArticle;
use Tests\Functional\ValidationTestCase;

/**
 * @group semantic_link
 */
class SyncCompanyArticleSemanticLinksWithTextCommandValidationTest extends ValidationTestCase
{
    public function testArticleFound(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompanyArticle::class,
        ])->getReferenceRepository();

        /** @var CompanyArticle $companyArticle */
        $companyArticle = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompanyArticle::REFERENCE_NAME);

        $command = new SyncCompanyArticleSemanticLinksWithTextCommand($companyArticle->getId());

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testArticleNotFound(): void
    {
        $command = new SyncCompanyArticleSemanticLinksWithTextCommand(0005);

        $this->getValidator()->validate($command);

        $this->assertNotEmpty($this->getValidator()->getLastErrors());
        $this->assertFieldInvalid('companyArticleId', 'Запись не найдена.');
    }
}

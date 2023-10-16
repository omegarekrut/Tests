<?php

namespace Tests\Functional\Domain\Record\CompanyArticle\Command\SocialNetwork;

use App\Domain\Record\CompanyArticle\Command\SocialNetwork\PublishCompanyArticleInSocialNetworkCommand;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWithAuthor;
use Tests\Functional\ValidationTestCase;

/**
 * @group company-article-social-network
 */
final class PublishCompanyArticleInSocialNetworkCommandTest extends ValidationTestCase
{
    public function testValidationNotPassedForIncorrectIdFilledCommand(): void
    {
        $command = new PublishCompanyArticleInSocialNetworkCommand(2);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('companyArticleId', 'Запись не найдена.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyArticleWithAuthor::class,
        ])->getReferenceRepository();

        $companyArticle = $referenceRepository->getReference(LoadCompanyArticleWithAuthor::REFERENCE_NAME);
        assert($companyArticle instanceof CompanyArticle);

        $command = new PublishCompanyArticleInSocialNetworkCommand($companyArticle->getId());

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}

<?php

namespace Tests\Controller\Company;

use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWithAuthor;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class CompanyArticleHideTest extends TestCase
{
    private const HIDE_PAGE_URI_TEMPLATE = 'company-articles/%d/hide/';
    private const VIEW_PAGE_URI_TEMPLATE = 'company-articles/view/%d/';

    public function testAdminCanHideCompanyArticle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyArticleWithAuthor::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $companyArticle = $referenceRepository->getReference(LoadCompanyArticleWithAuthor::REFERENCE_NAME);
        assert($companyArticle instanceof CompanyArticle);

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);

        $url = sprintf(self::HIDE_PAGE_URI_TEMPLATE, $companyArticle->getId());
        $client->request('GET', $url);
        $viewPage = $client->followRedirect();
        $this->assertSeeAlertInPageContent('success', 'Запись успешно скрыта.', $viewPage->html());

        $url = sprintf(self::VIEW_PAGE_URI_TEMPLATE, $companyArticle->getId());
        $client->request('GET', $url);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testOwnerCantHideCompanyArticle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyArticleWithAuthor::class,
        ])->getReferenceRepository();

        $companyArticle = $referenceRepository->getReference(LoadCompanyArticleWithAuthor::REFERENCE_NAME);
        assert($companyArticle instanceof CompanyArticle);

        $user = $companyArticle->getAuthor();

        $client = $this->getBrowser()->loginUser($user);

        $url = sprintf(self::HIDE_PAGE_URI_TEMPLATE, $companyArticle->getId());
        $client->request('GET', $url);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testNotOwnerCantHideCompanyArticle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyArticleWithAuthor::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $companyArticle = $referenceRepository->getReference(LoadCompanyArticleWithAuthor::REFERENCE_NAME);
        assert($companyArticle instanceof CompanyArticle);

        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);

        $url = sprintf(self::HIDE_PAGE_URI_TEMPLATE, $companyArticle->getId());
        $client->request('GET', $url);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }
}

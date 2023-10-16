<?php

namespace Tests\Controller\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;

class CompanyReviewControllerTest extends TestCase
{
    private const COMPANY_REVIEW_PAGE_TITLE = 'Добавить отзыв на компанию %s';
    private const COMPANY_REVIEW_TEXT = 'This company is great. I like it very much. I will recommend it to my friends. I will come back here again.';

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();

        $this->company = $this->loadCompanyWithOwner();
    }

    public function testAllowCreateCompanyReviewForNonOwnerOfCompany(): void
    {
        $user = $this->loadUserNotFromCompany();
        $browser = $this->getBrowser();

        $client = $browser->loginUser($user);
        $createCompanyReviewPage = $client->request('GET', self::getCreateCompanyReviewPageUrl($this->company));

        $this->assertEquals(Response::HTTP_OK, $browser->getResponse()->getStatusCode());
        $this->assertStringContainsString(sprintf(self::COMPANY_REVIEW_PAGE_TITLE, $this->company->getName()), $createCompanyReviewPage->html());

        $browser->submitForm('Опубликовать', [
            'company_review[text]' => self::COMPANY_REVIEW_TEXT,
        ]);

        $this->assertTrue($browser->getResponse()->isRedirect(self::getCompanyReviewsPageUrl($this->company)));

        $companyReviewsPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Отзыв успешно добавлен.', $companyReviewsPage->html());
    }

    public function testDenyCreateCompanyReviewForCompanyOwner(): void
    {
        $client = $this->getBrowser()->loginUser($this->company->getOwner());
        $client->request('GET', self::getCreateCompanyReviewPageUrl($this->company));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    private function loadCompanyWithOwner(): Company
    {
        return $this->loadFixture(LoadCompanyWithOwner::class, Company::class);
    }

    private function loadUserNotFromCompany(): User
    {
        return $this->loadFixture(LoadUserWithAvatar::class, User::class);
    }

    private static function getCreateCompanyReviewPageUrl(Company $company): string
    {
        return sprintf('/company-reviews/%s/%s/create/', $company->getSlug(), $company->getShortUuid());
    }

    private static function getCompanyReviewsPageUrl(Company $company): string
    {
        return sprintf('/companies/%s/%s/#reviews', $company->getSlug(), $company->getShortUuid());
    }
}

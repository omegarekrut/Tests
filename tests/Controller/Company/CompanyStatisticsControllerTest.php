<?php

namespace Controller\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Company\CompanyWithSubscription\LoadCompanyWithActiveSubscriptionWithUser;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;

class CompanyStatisticsControllerTest extends TestCase
{
    private const STATISTIC_PAGE_TITLE = 'Статистика карточки компании %s';
    private const STATISTICS_DATA_CHARTS_KEY = 'charts';

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();
    }

    public function testAllowAccessToStatisticsPageForOwnerOfCompanyWithoutSubscription()
    {
        $company = $this->loadCompanyWithSubscription();

        $client = $this->getBrowser()->loginUser($company->getOwner());
        $companyStatisticsPage = $client->request('GET', self::getCompanyStatisticsViewPageUrl($company));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(sprintf(self::STATISTIC_PAGE_TITLE, $company->getName()), $companyStatisticsPage->html());
    }

    public function testDenyAccessToStatisticsPageForOwnerOfCompanyWithoutSubscription()
    {
        $company = $this->loadCompanyWithoutSubscription();

        $client = $this->getBrowser()->loginUser($company->getOwner());
        $client->request('GET', self::getCompanyStatisticsViewPageUrl($company));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testDenyAccessToStatisticsPageForUserNotFromCompany()
    {
        $company = $this->loadCompanyWithSubscription();
        $user = $this->loadUserNotFromCompany();

        $client = $this->getBrowser()->loginUser($user);
        $client->request('GET', self::getCompanyStatisticsViewPageUrl($company));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testDenyAccessToStatisticsPageForGuest()
    {
        $company = $this->loadCompanyWithSubscription();

        $client = $this->getBrowser();
        $client->request('GET', self::getCompanyStatisticsViewPageUrl($company));

        $this->assertEquals(Response::HTTP_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAllowAccessToStatisticsDataForOwnerOfCompanyWithSubscription()
    {
        $company = $this->loadCompanyWithSubscription();

        $client = $this->getBrowser()->loginUser($company->getOwner());
        $client->xmlHttpRequest('GET', self::getCompanyStatisticsDataUrl($company));
        $decodedResponseContent = json_decode($this->getBrowser()->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertArrayHasKey(self::STATISTICS_DATA_CHARTS_KEY, $decodedResponseContent);
    }

    public function testDenyAccessToStatisticsDataForOwnerOfCompanyWithoutSubscription()
    {
        $company = $this->loadCompanyWithoutSubscription();

        $client = $this->getBrowser()->loginUser($company->getOwner());
        $client->xmlHttpRequest('GET', self::getCompanyStatisticsDataUrl($company));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testDenyAccessToStatisticsDataForUserNotFromCompany()
    {
        $company = $this->loadCompanyWithSubscription();
        $user = $this->loadUserNotFromCompany();

        $client = $this->getBrowser()->loginUser($user);
        $client->xmlHttpRequest('GET', self::getCompanyStatisticsDataUrl($company));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testDenyAccessToStatisticsDataForGuest()
    {
        $company = $this->loadCompanyWithSubscription();

        $client = $this->getBrowser();
        $client->xmlHttpRequest('GET', self::getCompanyStatisticsDataUrl($company));

        $this->assertEquals(Response::HTTP_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }

    private function loadCompanyWithoutSubscription(): Company
    {
        return $this->loadFixture(LoadCompanyWithOwner::class, Company::class);
    }

    private function loadCompanyWithSubscription(): Company
    {
        return $this->loadFixture(LoadCompanyWithActiveSubscriptionWithUser::class, Company::class);
    }

    private function loadUserNotFromCompany(): User
    {
        return $this->loadFixture(LoadUserWithAvatar::class, User::class);
    }

    private static function getCompanyStatisticsViewPageUrl(Company $company): string
    {
        return sprintf('/companies/%s/%s/statistics/', $company->getSlug(), $company->getShortUuid());
    }

    private static function getCompanyStatisticsDataUrl(Company $company): string
    {
        return sprintf('/companies/%s/%s/statistics-data/', $company->getSlug(), $company->getShortUuid());
    }
}

<?php

namespace Tests\Controller\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Company\CompanyWithSubscription\LoadCompanyWithActiveSubscriptionWithUser;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;

/**
 * @group company-controller
 */
class CompanyControllerTest extends TestCase
{
    private const STATISTICS_MENU_ACTION_NAME = 'Статистика';
    private const STATISTICS_CARD_TITLE = 'Статистика за месяц';
    private const BUTTON_SUBSCRIPTION_TILE_LINK = 'https://business.fishingsib.ru/?utm_source=fishingsib&utm_medium=companies&utm_campaign=button_choose#тарифы';

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();
    }

    public function testCompanyOwnerCanSeeStatisticsButton(): void
    {
        $company = $this->loadOwenedCompanyWithSubscription();

        $client = $this->getBrowser()->loginUser($company->getOwner());
        $companyViewPage = $client->request('GET', self::getCompanyViewPageUrl($company));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::STATISTICS_MENU_ACTION_NAME, $companyViewPage->html());
    }

    public function testOwnerOfCompanyWithoutSubscriptionCannotSeeStatisticsCard(): void
    {
        $company = $this->loadCompanyWithoutSubscription();

        try {
            Carbon::setTestNow(Carbon::parse('2023-08-01 09:00:00'));

            $client = $this->getBrowser()->loginUser($company->getOwner());
            $companyViewPage = $client->request('GET', self::getCompanyViewPageUrl($company));
        } finally {
            Carbon::setTestNow();
        }

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringNotContainsString(self::STATISTICS_CARD_TITLE, $companyViewPage->html());
    }

    public function testNotCompanyOwnerCannotSeeStatisticsButton(): void
    {
        $userWithoutCompany = $this->loadUserWithoutCompany();
        $company = $this->loadOwenedCompanyWithSubscription();

        $client = $this->getBrowser()->loginUser($userWithoutCompany);
        $crawler = $client->request('GET', self::getCompanyViewPageUrl($company));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringNotContainsString(self::STATISTICS_MENU_ACTION_NAME, $crawler->html());
    }

    public function testCompanyOwnerCanSeeSubscriptionLink(): void
    {
        $company = $this->loadCompanyWithoutSubscription();

        $client = $this->getBrowser()->loginUser($company->getOwner());

        $crawler = $client->request('GET', self::getCompanyViewPageUrl($company));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringNotContainsString(self::BUTTON_SUBSCRIPTION_TILE_LINK, $crawler->html());
    }

    private function loadOwenedCompanyWithSubscription(): Company
    {
        return $this->loadFixture(LoadCompanyWithActiveSubscriptionWithUser::class, Company::class);
    }

    private function loadCompanyWithoutSubscription(): Company
    {
        return $this->loadFixture(LoadCompanyWithOwner::class, Company::class);
    }

    private function loadUserWithoutCompany(): User
    {
        return $this->loadFixture(LoadUserWithAvatar::class, User::class);
    }

    private static function getCompanyViewPageUrl(Company $company): string
    {
        return sprintf('/companies/%s/%s/', $company->getSlug(), $company->getShortUuid());
    }
}

<?php

namespace Tests\Controller\Admin\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Generator;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwner;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Company\CompanyWithSubscription\LoadCompanyWithActiveSubscription;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;

/**
 * @group admin-company-controller
 */
class CompanyControllerTest extends TestCase
{
    const COMPANY_INDEX_PATH = '/admin/company/';
    const COMPANY_SEARCH_SUBMIT_BUTTON = 'Поиск';
    const COMPANY_WHICH_DOESNT_EXIST_NAME = 'company-which-doesnt-exist';
    const EMPTY_RESULT_MESSAGE = 'Не найдено ни одной компании';

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();
    }

    public function testAdminAccessAllowedIndex(): void
    {
        $browser = $this->getBrowser()->loginUser($this->loadAdmin());
        $viewPage = $browser->request('GET', self::COMPANY_INDEX_PATH);

        $this->assertStringContainsString('Список компаний', $viewPage->html());
        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider getUserFixturesWhoDoNotHaveAccessToCompanyList
     */
    public function testAccessDeniedIndex(string $singleFixtureClass): void
    {
        $user = $this->loadFixture($singleFixtureClass, User::class);

        $browser = $this->getBrowser()->loginUser($user);
        $browser->request('GET', self::COMPANY_INDEX_PATH);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testCompanySearchFilterByCompanyName(): void
    {
        $admin = $this->loadAdmin();

        $companyWithActiveSubscription = $this->loadCompanyWithActiveSubscription();
        $companyWithoutOwner = $this->loadCompanyWithoutOwner();

        $expectedCompanyName = $companyWithoutOwner->getName();
        $unexpectedCompanyName = $companyWithActiveSubscription->getName();

        $browser = $this->getBrowser()->loginUser($admin);
        $browser->request('GET', self::COMPANY_INDEX_PATH);

        $viewPage = $browser->submitForm(self::COMPANY_SEARCH_SUBMIT_BUTTON, [
            'admin_company_search[companySearch]' => $expectedCompanyName,
        ], 'GET');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString($expectedCompanyName, $viewPage->filter('tbody')->html());
        $this->assertStringNotContainsString($unexpectedCompanyName, $viewPage->filter('tbody')->html());
    }

    public function testCompanySearchFilterByActiveSubscription(): void
    {
        $admin = $this->loadAdmin();

        $companyWithActiveSubscription = $this->loadCompanyWithActiveSubscription();
        $companyWithoutOwner = $this->loadCompanyWithoutOwner();

        $expectedCompanyName = $companyWithActiveSubscription->getName();
        $unexpectedCompanyName = $companyWithoutOwner->getName();

        $browser = $this->getBrowser()->loginUser($admin);
        $browser->request('GET', self::COMPANY_INDEX_PATH);

        $viewPage = $browser->submitForm(self::COMPANY_SEARCH_SUBMIT_BUTTON, [
            'admin_company_search[hasActiveSubscription]' => 1,
        ], 'GET');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString($expectedCompanyName, $viewPage->filter('tbody')->html());
        $this->assertStringNotContainsString($unexpectedCompanyName, $viewPage->filter('tbody')->html());

        $resetLink = $viewPage->selectLink('Сбросить')->link();
        $viewPage = $browser->click($resetLink);

        $expectedCompanyNames = [
            $companyWithActiveSubscription->getName(),
            $companyWithoutOwner->getName(),
        ];

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        foreach ($expectedCompanyNames as $expectedCompanyName) {
            $this->assertStringContainsString($expectedCompanyName, $viewPage->filter('tbody')->html());
        }
    }

    public function testCompanySearchEmptyResults(): void
    {
        $admin = $this->loadAdmin();

        $this->loadFixtures([
            LoadCompanyWithActiveSubscription::class,
            LoadCompanyWithoutOwner::class,
        ], true);

        $browser = $this->getBrowser()->loginUser($admin);
        $browser->request('GET', self::COMPANY_INDEX_PATH);

        $viewPage = $browser->submitForm(self::COMPANY_SEARCH_SUBMIT_BUTTON, [
            'admin_company_search[companySearch]' => self::COMPANY_WHICH_DOESNT_EXIST_NAME,
            'admin_company_search[hasActiveSubscription]' => 1,
        ], 'GET');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::EMPTY_RESULT_MESSAGE, $viewPage->filter('tbody')->html());
    }

    public function testRewriteOwner(): void
    {
        $admin = $this->loadAdmin();

        $company = $this->loadCompanyWithOwner();

        $browser = $this->getBrowser()->loginUser($admin);
        $viewPage = $browser->request('GET', self::COMPANY_INDEX_PATH);

        $rewriteOwnerLink = $viewPage->filter('[title="Назначить владельца"]')->link();
        $browser->click($rewriteOwnerLink);

        $browser->submitForm('Сохранить', [
            'owner' => $admin->getUsername(),
        ]);

        $this->assertTrue($browser->getResponse()->isRedirect(self::COMPANY_INDEX_PATH));

        $viewPage = $browser->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertSeeAlertInPageContent('success', 'Владелец компании успешно обновлен.', $viewPage->html());

        $viewPage = $browser->submitForm(self::COMPANY_SEARCH_SUBMIT_BUTTON, [
            'admin_company_search[companySearch]' => $company->getName(),
        ], 'GET');

        $this->assertStringContainsString($admin->getUsername(), $viewPage->filter('tbody')->html());
    }

    /**
     * @return Generator<class-string<SingleReferenceFixtureInterface>>
     */
    public function getUserFixturesWhoDoNotHaveAccessToCompanyList(): Generator
    {
        yield [LoadModeratorUser::class];

        yield [LoadUserWithoutRecords::class];
    }

    private function loadAdmin(): User
    {
        return $this->loadFixture(LoadAdminUser::class, User::class);
    }

    private function loadCompanyWithActiveSubscription(): Company
    {
        return $this->loadFixture(LoadCompanyWithActiveSubscription::class, Company::class);
    }

    private function loadCompanyWithoutOwner(): Company
    {
        return $this->loadFixture(LoadCompanyWithoutOwner::class, Company::class);
    }

    private function loadCompanyWithOwner(): Company
    {
        return $this->loadFixture(LoadCompanyWithOwner::class, Company::class);
    }
}

<?php

namespace Tests\Controller\Admin\Company;

use App\Domain\Company\Entity\OwnershipRequest;
use App\Domain\User\Entity\User;
use Generator;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Company\OwnershipRequest\LoadOwnershipRequestToFutureApprove;
use Tests\DataFixtures\ORM\Company\OwnershipRequest\LoadOwnershipRequestToFutureReject;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;

/**
 * @group admin-company-controller
 */
class OwnershipRequestControllerTest extends TestCase
{
    const OWNERSHIP_REQUEST_INDEX_PATH = '/admin/company-ownership-request/';
    const SEARCH_OWNERSHIP_REQUEST_SUBMIT_BUTTON = 'Поиск';

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();
    }

    public function testAdminAccessAllowedIndex(): void
    {
        $browser = $this->getBrowser()->loginUser($this->loadAdmin());
        $browser->request('GET', self::OWNERSHIP_REQUEST_INDEX_PATH);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider getUserFixturesWhoDoNotHaveAccessToOwnershipRequestsCases
     */
    public function testAccessDeniedIndex(string $singleFixtureClass): void
    {
        $user = $this->loadFixture($singleFixtureClass, User::class);

        $browser = $this->getBrowser()->loginUser($user);
        $browser->request('GET', self::OWNERSHIP_REQUEST_INDEX_PATH);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testOwnershipRequestSearchFilterByCompany(): void
    {
        $admin = $this->loadAdmin();

        $ownershipRequestToApprove = $this->loadOwnershipRequestToFutureApprove();
        $ownershipRequestToReject = $this->loadOwnershipRequestToFutureReject();

        $companyIdToFilter = $ownershipRequestToApprove->getCompany()->getId();
        $expectedCompanyName = $ownershipRequestToApprove->getCompany()->getName();
        $unexpectedCompanyName = $ownershipRequestToReject->getCompany()->getName();

        $browser = $this->getBrowser()->loginUser($admin);
        $browser->request('GET', self::OWNERSHIP_REQUEST_INDEX_PATH);

        $viewPage = $browser->submitForm(self::SEARCH_OWNERSHIP_REQUEST_SUBMIT_BUTTON, [
            'ownership_request_search[company]' => $companyIdToFilter,
        ], 'GET');

        $this->assertStringContainsString($expectedCompanyName, $viewPage->filter('tbody')->html());
        $this->assertStringNotContainsString($unexpectedCompanyName, $viewPage->filter('tbody')->html());

        $resetLink = $viewPage->selectLink('Сбросить')->link();
        $viewPage = $browser->click($resetLink);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $expectedCompanyNames = [$ownershipRequestToApprove->getCompany()->getName(), $ownershipRequestToReject->getCompany()->getName()];

        foreach ($expectedCompanyNames as $expectedCompanyName) {
            $this->assertStringContainsString($expectedCompanyName, $viewPage->filter('tbody')->html());
        }
    }

    public function testApproveOwnershipRequest(): void
    {
        $admin = $this->loadAdmin();

        $ownershipRequestToApprove = $this->loadOwnershipRequestToFutureApprove();

        $browser = $this->getBrowser()->loginUser($admin);
        $viewPage = $browser->request('GET', self::OWNERSHIP_REQUEST_INDEX_PATH);

        $approveLink = $viewPage->filter('[title="Подтвердить"]')->link();
        $browser->click($approveLink);

        $viewPage = $browser->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertSeeAlertInPageContent('success', 'Запрос подтвержден.', $viewPage->html());
        $this->assertStringNotContainsString($ownershipRequestToApprove->getCompany()->getName(), $viewPage->filter('tbody')->html());
    }

    public function testRejectOwnershipRequest(): void
    {
        $admin = $this->loadAdmin();

        $ownershipRequestToReject = $this->loadOwnershipRequestToFutureReject();

        $browser = $this->getBrowser()->loginUser($admin);
        $viewPage = $browser->request('GET', self::OWNERSHIP_REQUEST_INDEX_PATH);

        $rejectLink = $viewPage->filter('[title="Отклонить"]')->link();
        $browser->click($rejectLink);

        $viewPage = $browser->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertSeeAlertInPageContent('success', 'Запрос отклонен.', $viewPage->html());
        $this->assertStringNotContainsString($ownershipRequestToReject->getCompany()->getName(), $viewPage->filter('tbody')->html());
    }

    /**
     * @return Generator<class-string<SingleReferenceFixtureInterface>>
     */
    public function getUserFixturesWhoDoNotHaveAccessToOwnershipRequestsCases(): Generator
    {
        yield [LoadModeratorUser::class];

        yield [LoadUserWithoutRecords::class];
    }

    private function loadAdmin(): User
    {
        return $this->loadFixture(LoadAdminUser::class, User::class);
    }

    private function loadOwnershipRequestToFutureApprove(): OwnershipRequest
    {
        return $this->loadFixture(LoadOwnershipRequestToFutureApprove::class, OwnershipRequest::class);
    }

    private function loadOwnershipRequestToFutureReject(): OwnershipRequest
    {
        return $this->loadFixture(LoadOwnershipRequestToFutureReject::class, OwnershipRequest::class);
    }
}

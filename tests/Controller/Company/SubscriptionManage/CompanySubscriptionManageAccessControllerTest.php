<?php

namespace Tests\Controller\Company\SubscriptionManage;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwner;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;

/**
 * @group company
 */
class CompanySubscriptionManageAccessControllerTest extends TestCase
{
    private const TITLE = 'Управление подпиской';

    public function testAllowAccessAsOwnerToSubscriptionControl(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $user = $company->getOwner();
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);
        $url = sprintf('/companies/%s/%s/subscription-control/', $company->getSlug(), $company->getShortUuid());

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::TITLE, $page->filter('h1')->text());
    }

    public function testAllowAccessAsAdminToSubscriptionControl(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);
        $url = sprintf('/companies/%s/%s/subscription-control/', $company->getSlug(), $company->getShortUuid());

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::TITLE, $page->filter('h1')->text());
    }

    public function testDenyAccessAsModeratorToSubscriptionControl(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
            LoadModeratorUser::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $user = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);
        $url = sprintf('/companies/%s/%s/subscription-control/', $company->getSlug(), $company->getShortUuid());

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringNotContainsString(self::TITLE, $page->filter('h1')->text());
    }

    public function testDenyAccessAsNotOwnerToSubscriptionControl(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithoutOwner::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithoutOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);
        $url = sprintf('/companies/%s/%s/subscription-control/', $company->getSlug(), $company->getShortUuid());

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringNotContainsString(self::TITLE, $page->filter('h1')->text());
    }
}

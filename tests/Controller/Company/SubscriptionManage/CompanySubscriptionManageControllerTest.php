<?php

namespace Tests\Controller\Company\SubscriptionManage;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Company\Company\LoadUnsubscribedFromNewsletterCompany;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

/**
 * @group company
 */
class CompanySubscriptionManageControllerTest extends TestCase
{
    private const UNSUBSCRIBED_JSON_CONTENT = '{"status":false}';
    private const SUBSCRIBED_JSON_CONTENT = '{"status":true}';


    public function testUnsubscribeFromNewsletter(): void
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

        $link = $page->filter('switch-component')->attr('link-for-deactivate');

        $client->request('GET', $link, [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $this->assertStatusCode(Response::HTTP_OK, $client);
        $this->assertStringContainsString(self::UNSUBSCRIBED_JSON_CONTENT, $client->getResponse()->getContent());
    }

    public function testSubscribeToNewsletter(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUnsubscribedFromNewsletterCompany::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadUnsubscribedFromNewsletterCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);

        $url = sprintf('/companies/%s/%s/subscription-control/', $company->getSlug(), $company->getShortUuid());
        $page = $client->request('GET', $url);

        $link = $page->filter('switch-component')->attr('link-for-activate');

        $client->request('GET', $link, [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $this->assertStatusCode(Response::HTTP_OK, $client);
        $this->assertStringContainsString(self::SUBSCRIBED_JSON_CONTENT, $client->getResponse()->getContent());
    }
}

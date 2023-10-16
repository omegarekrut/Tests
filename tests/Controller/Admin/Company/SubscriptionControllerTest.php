<?php

namespace Tests\Controller\Admin\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use DateInterval;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwner;
use Tests\DataFixtures\ORM\Company\CompanyWithSubscription\LoadCompanyWithActiveSubscription;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

/**
 * @group admin-company-controller
 */
class SubscriptionControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadCompanyWithoutOwner::class
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $company = $referenceRepository->getReference(LoadCompanyWithoutOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $client = $this->getBrowser()->loginUser($user);

        $client->request('GET', sprintf('/admin/company/%s/subscriptions/', $company->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testCreate(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadCompanyWithoutOwner::class
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $company = $referenceRepository->getReference(LoadCompanyWithoutOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $client = $this->getBrowser()->loginUser($user);

        $client->request('GET', sprintf('/admin/company/%s/subscriptions/create/', $company->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $externalPaymentId = '#123';
        $comment = 'Comment';

        $client->submitForm('Сохранить', [
            'company_subscription[startedAt]' => '2023-03-02',
            'company_subscription[expiredAt]' => '2023-03-03',
            'company_subscription[externalPaymentId]' => $externalPaymentId,
            'company_subscription[comment]' => $comment,
        ]);

        $this->assertTrue($client->getResponse()->isRedirect(sprintf('/admin/company/%s/subscriptions/', $company->getId())));

        $indexPage = $client->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Подписка компании успешно добавлена.', $indexPage->html());

        $this->assertStringContainsString($externalPaymentId, $indexPage->html());
        $this->assertStringContainsString($comment, $indexPage->html());
    }

    public function testEdit(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadCompanyWithActiveSubscription::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $companyWithSubscription = $referenceRepository->getReference(LoadCompanyWithActiveSubscription::REFERENCE_NAME);
        assert($companyWithSubscription instanceof Company);

        $subscription = $companyWithSubscription->getSubscriptions()->first();

        $client = $this->getBrowser()->loginUser($user);

        $client->request('GET', sprintf('/admin/company/%s/subscriptions/%s/edit/', $companyWithSubscription->getId(), $subscription->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $externalPaymentId = '#123 Updated';
        $comment = 'Updated comment';

        $client->submitForm('Сохранить', [
            'company_subscription[expiredAt]' => $subscription->getExpiredAt()->add(new DateInterval('P1D'))->format('Y-m-d'),
            'company_subscription[externalPaymentId]' => $externalPaymentId,
            'company_subscription[comment]' => $comment,
        ]);

        $this->assertTrue($client->getResponse()->isRedirect(sprintf('/admin/company/%s/subscriptions/', $companyWithSubscription->getId())));

        $indexPage = $client->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Подписка компании успешно обновлена.', $indexPage->html());

        $this->assertStringContainsString($externalPaymentId, $indexPage->html());
        $this->assertStringContainsString($comment, $indexPage->html());
    }

    public function testDelete(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadCompanyWithActiveSubscription::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $companyWithSubscription = $referenceRepository->getReference(LoadCompanyWithActiveSubscription::REFERENCE_NAME);
        assert($companyWithSubscription instanceof Company);

        $subscription = $companyWithSubscription->getSubscriptions()->first();

        $client = $this->getBrowser()->loginUser($user);

        $client->request('GET', sprintf('/admin/company/%s/subscriptions/%s/delete/', $companyWithSubscription->getId(), $subscription->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertEquals('{"status":"ok"}', $this->getBrowser()->getResponse()->getContent());
    }
}

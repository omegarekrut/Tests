<?php

namespace Tests\Controller\Admin\CompanyLetter;

use App\Domain\CompanyLetter\Entity\CompanyLetter;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\CompanyLetter\LoadCompanyLetterForPreviousMonth;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

class CompanyLetterControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);

        $client->request('GET', '/admin/company/');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyLetterForPreviousMonth::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $companyLetter = $referenceRepository->getReference(LoadCompanyLetterForPreviousMonth::REFERENCE_NAME);
        assert($companyLetter instanceof CompanyLetter);

        $client = $this->getBrowser()
            ->loginUser($user);

        $crawler = $client->request('GET', sprintf('/admin/company-letter/view/%d/', $companyLetter->getNumber()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString($companyLetter->getGreetingBlock()->getData(), $crawler->html());
        $this->assertStringContainsString($companyLetter->getInnovationBlocks()[0]->getData(), $crawler->html());
    }
}

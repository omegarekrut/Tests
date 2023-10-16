<?php

namespace Tests\Controller\Api;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;

class CompanyAuthorControllerTest extends TestCase
{
    private Company $company;
    private User $companyOwner;
    private Session $session;

    public function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        $this->companyOwner = $this->company->getOwner();
        $this->session = $this->getContainer()->get('session');
    }

    public function testSaveCompanyAuthorInSession(): void
    {
        $browser = $this->getBrowser()->loginUser($this->companyOwner);
        $browser->xmlHttpRequest(
            'POST',
            sprintf('/api/company-author/change/%s/', $this->company->getId()),
        );

        $expectedSession = $this->session->get('session_storage_company_author_id');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertEquals((string) $this->company->getId(), $expectedSession);
    }

    public function testClearCompanyAuthorInSession(): void
    {
        $browser = $this->getBrowser()->loginUser($this->companyOwner);
        $session = $this->getContainer()->get('session');
        $session->set('session_storage_company_author_id', $this->company->getId());

        $browser->xmlHttpRequest(
            'POST',
            '/api/company-author/clear/',
        );

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertNull($this->session->get('session_storage_company_author_id'));
    }
}

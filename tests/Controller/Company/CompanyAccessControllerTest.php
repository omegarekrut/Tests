<?php

namespace Tests\Controller\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Rubric;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Company\Rubric\LoadDefaultRubric;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class CompanyAccessControllerTest extends TestCase
{
    public function testFindCompaniesByAuthorName(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
            LoadTestUser::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $author = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($author instanceof User);

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);

        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $client = $this->getBrowser()->loginUser($admin);
        $url = sprintf("/admin/company/find-by-author/?q=%s", $author->getUsername());

        $client->xmlHttpRequest('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString($company->getId()->toString(), $this->getBrowser()->getResponse()->getContent());
        $this->assertStringContainsString($company->getName(), $this->getBrowser()->getResponse()->getContent());
    }

    public function testFindByNotExistAuthorName(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);

        $client = $this->getBrowser()->loginUser($admin);
        $url = '/admin/company/find-by-author/?q=wrongUserName';

        $client->xmlHttpRequest('GET', $url);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAllowOnAjaxCompanyListForGuest(): void
    {
        $client = $this->getBrowser();
        $url = '/companies/ajax-company-list';

        $client->xmlHttpRequest('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAllowOnAjaxCompaniesByRubricForGuest(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadDefaultRubric::class,
        ])->getReferenceRepository();

        $rubric = $referenceRepository->getReference(LoadDefaultRubric::REFERENCE_NAME);
        assert($rubric instanceof Rubric);

        $client = $this->getBrowser();
        $url = sprintf('/companies/ajax-rubric/%s/', $rubric->getSlug());

        $client->xmlHttpRequest('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }
}

<?php

namespace Tests\Unit\Auth\Visitor\CompanyAuthor;

use App\Auth\Visitor\CompanyAuthor\CompanyAuthorInSessionStorage;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Repository\CompanyRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Tests\Functional\TestCase;

class CompanyAuthorInSessionStorageTest extends TestCase
{
    private Company $companyMock;
    private SessionInterface $session;
    private CompanyAuthorInSessionStorage $companyAuthorInSessionStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $uuidMock = $this->createMock(Uuid::class);
        $this->companyMock = $this->createMock(Company::class);
        $this->companyMock->method('getId')->willReturn($uuidMock);

        $companyRepositoryMock = $this->createMock(CompanyRepository::class);
        $companyRepositoryMock->method('findById')->willReturn($this->companyMock);

        $this->session = new Session();

        $this->companyAuthorInSessionStorage = new CompanyAuthorInSessionStorage($this->session, $companyRepositoryMock);
        $this->companyAuthorInSessionStorage->saveCompanyAuthorToSession($this->companyMock);
    }

    public function testGetCompanyAuthorInSessionStorage(): void
    {
        $this->assertEquals($this->companyMock, $this->companyAuthorInSessionStorage->getCompanyAuthorFromSession());
    }

    public function testRemoveCompanyAuthorInSessionStorage(): void
    {
        $this->companyAuthorInSessionStorage->removeCompanyAuthorFromSession();

        $this->assertNull($this->companyAuthorInSessionStorage->getCompanyAuthorFromSession());
    }

    public function testGetCompanyAuthorFromSessionStorageAfterClearSessionShouldBeNull(): void
    {
        $this->session->clear();

        $this->assertNull($this->companyAuthorInSessionStorage->getCompanyAuthorFromSession());
    }
}

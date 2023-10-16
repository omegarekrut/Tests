<?php

namespace Tests\Unit\Domain\CompanyLetter\Service;

use App\Domain\CompanyLetter\Entity\CompanyLetter;
use App\Domain\CompanyLetter\Repository\CompanyLetterRepository;
use App\Domain\CompanyLetter\Service\CompanyLetterNumberGenerator;
use Tests\Unit\TestCase;

class CompanyLetterNumberGeneratorTest extends TestCase
{
    public function testGetNewCompanyLetterNumber(): void
    {
        $companyLetterMock = $this->createMock(CompanyLetter::class);
        $companyLetterMock
            ->method('getNumber')
            ->willReturn(1);

        $companyLetterRepositoryMock = $this->getCompanyLetterRepository($companyLetterMock);

        $companyLetterNumberGenerator = new CompanyLetterNumberGenerator($companyLetterRepositoryMock);

        $this->assertEquals(2, $companyLetterNumberGenerator->getNewCompanyLetterNumber());
    }

    public function testGetNewCompanyLetterNumberWhenDatabaseEmpty(): void
    {
        $companyLetterRepositoryMock = $this->getCompanyLetterRepository(null);

        $companyLetterNumberGenerator = new CompanyLetterNumberGenerator($companyLetterRepositoryMock);

        $this->assertEquals(1, $companyLetterNumberGenerator->getNewCompanyLetterNumber());
    }

    private function getCompanyLetterRepository(?CompanyLetter $companyLetterMock): CompanyLetterRepository
    {
        $companyLetterRepositoryMock = $this->createMock(CompanyLetterRepository::class);
        $companyLetterRepositoryMock
            ->method('findLastCompanyLetter')
            ->willReturn($companyLetterMock);

        return $companyLetterRepositoryMock;
    }
}

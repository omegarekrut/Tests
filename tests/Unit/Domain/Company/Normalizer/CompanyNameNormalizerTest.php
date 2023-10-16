<?php

namespace Tests\Unit\Domain\Company\Normalizer;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Contact;
use App\Domain\Company\Entity\Location;
use App\Domain\Company\Normalizer\CompanyNameNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @group company
 */
class CompanyNameNormalizerTest extends TestCase
{
    private CompanyNameNormalizer $companyNameNormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyNameNormalizer = new CompanyNameNormalizer();
    }

    protected function tearDown(): void
    {
        unset($this->companyNameNormalizer);

        parent::tearDown();
    }

    public function testNormalize(): void
    {
        $companyNames = [
            $this->getMockCompany('company1'),
            $this->getMockCompany('company2'),
        ];

        $expectedNormalizedData = [
            'companyNames' => [
                ['companyName' => 'company1'],
                ['companyName' => 'company2'],
            ],
        ];

        $this->assertEquals($expectedNormalizedData, $this->companyNameNormalizer->normalize($companyNames));
    }

    public function testNormalizeWithEmptyArray(): void
    {
        $companyNames = [];

        $expectedNormalizedData = [
            'companyNames' => [],
        ];

        $this->assertEquals($expectedNormalizedData, $this->companyNameNormalizer->normalize($companyNames));
    }

    public function testNormalizeForAutocomplete(): void
    {
        $companies = [
            $this->getMockCompanyForAutocomplete('company1', 'slug1', 'shortUuid1', 'contact1'),
            $this->getMockCompanyForAutocomplete('company2', 'slug2', 'shortUuid2', 'contact2'),
        ];

        $expectedNormalizedData = [
            [
                'name' => 'company1',
                'slug' => 'slug1',
                'shortUuid' => 'shortUuid1',
                'contact' => 'contact1',
                ],
            [
                'name' => 'company2',
                'slug' => 'slug2',
                'shortUuid' => 'shortUuid2',
                'contact' => 'contact2',
                ],
            ];

        $this->assertEquals($expectedNormalizedData, $this->companyNameNormalizer->normalizeForAutocomplete($companies));
    }
    public function testNormalizeForAutocompleteWithEmptyArray(): void
    {
        $companies = [];

        $expectedNormalizedData = [];

        $this->assertEquals($expectedNormalizedData, $this->companyNameNormalizer->normalizeForAutocomplete($companies));
    }

    private function getMockCompany(string $name): Company
    {
        $mock = $this->createMock(Company::class);

        $mock
            ->method('getName')
            ->willReturn($name);

        return $mock;
    }

    private function getMockCompanyForAutocomplete(string $name, string $slug, string $shortUuid, string $contact): Company
    {
        $mock = $this->createMock(Company::class);

        $mock
            ->method('getName')
            ->willReturn($name);
        $mock
            ->method('getSlug')
            ->willReturn($slug);
        $mock
            ->method('getShortUuid')
            ->willReturn($shortUuid);
        $mock
            ->method('getContact')
            ->willReturn($this->getContact($contact));

        return $mock;
    }

    private function getContact(string $contact): Contact
    {
        $mock = $this->createMock(Contact::class);

        $mock
            ->method('getFirstLocation')
            ->willReturn($this->getLocation($contact));

        return $mock;
    }

    private function getLocation(string $contact): Location
    {
        $mock = $this->createMock(Location::class);

        $mock
            ->method('getAddress')
            ->willReturn($contact);

        return $mock;
    }
}

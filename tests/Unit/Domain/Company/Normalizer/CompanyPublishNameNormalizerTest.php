<?php

namespace Tests\Unit\Domain\Company\Normalizer;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Normalizer\CompanyPublishNameNormalizer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Unit\Domain\Rss\Record\Chooser\TestCase;

class CompanyPublishNameNormalizerTest extends TestCase
{
    private CompanyPublishNameNormalizer $companyPublishNameNormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyPublishNameNormalizer = new CompanyPublishNameNormalizer();
    }

    protected function tearDown(): void
    {
        unset($this->companyPublishNameNormalizer);

        parent::tearDown();
    }

    public function testNormalizeForAutocomplete(): void
    {
        $id = Uuid::uuid4();

        $companies = [
            $this->getMockCompanyForAutocomplete($id, 'company1'),
            $this->getMockCompanyForAutocomplete($id, 'company2'),
        ];

        $expectedNormalizedData = [
            [
                'id' => $id,
                'name' => 'company1',
            ],
            [
                'id' => $id,
                'name' => 'company2',
            ],
        ];

        $this->assertEquals($expectedNormalizedData, $this->companyPublishNameNormalizer->normalizeForAutocomplete($companies));
    }

    private function getMockCompanyForAutocomplete(UuidInterface $id, string $name): Company
    {
        $mock = $this->createMock(Company::class);

        $mock
            ->method('getId')
            ->willReturn($id);
        $mock
            ->method('getName')
            ->willReturn($name);

        return $mock;
    }
}

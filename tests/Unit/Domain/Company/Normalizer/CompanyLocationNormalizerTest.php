<?php

namespace Tests\Unit\Domain\Company\Normalizer;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Contact;
use App\Domain\Company\Entity\Location;
use App\Domain\Company\Normalizer\CompanyLocationNormalizer;
use App\Util\Coordinates\Coordinates;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Unit\TestCase;
use RuntimeException;

class CompanyLocationNormalizerTest extends TestCase
{
    private CompanyLocationNormalizer $companyLocationNormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyLocationNormalizer = new CompanyLocationNormalizer();
    }

    protected function tearDown(): void
    {
        unset($this->companyLocationNormalizer);

        parent::tearDown();
    }

    /**
     * @throws \Exception
     */
    public function testSerializeCompanyCoordinatesCollection(): void
    {
        $id = Uuid::uuid4();

        $companies = [
            $this->createCompany($id, new Coordinates('10', '20')),
            $this->createCompany($id, new Coordinates('30', '40')),
        ];

        $expectedNormalizedData = [
            [
                'id' => $id,
                'latitude' => 10,
                'longitude' => 20,
                'regionId' => null,
            ],
            [
                'id' => $id,
                'latitude' => 30,
                'longitude' => 40,
                'regionId' => null,
            ],
        ];

        $this->assertEquals($expectedNormalizedData, $this->companyLocationNormalizer->normalize($companies));
    }

    /**
     * @throws \Exception
     */
    public function testCompanyWithEmptyLocationThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Try to get location in company "" which has neither an address nor a point on the map.');

        $companies = [
            $this->createCompanyWithEmptyLocation(),
        ];

        $this->companyLocationNormalizer->normalize($companies);
    }

    private function createCompany(UuidInterface $id, Coordinates $coordinates): Company
    {
        $companyMock = $this->createMock(Company::class);

        $companyMock
            ->method('getId')
            ->willReturn($id);

        $companyMock
            ->method('getContact')
            ->willReturn($this->getContact($coordinates));

        return $companyMock;
    }

    private function getContact(Coordinates $coordinates): Contact
    {
        $contactMock = $this->createMock(Contact::class);

        $contactMock
            ->method('getFirstLocation')
            ->willReturn($this->getLocation($coordinates));

        return $contactMock;
    }

    private function getLocation(Coordinates $coordinates): Location
    {
        $locationMock = $this->createMock(Location::class);

        $locationMock
            ->method('getCoordinates')
            ->willReturn($coordinates);

        return $locationMock;
    }

    private function createCompanyWithEmptyLocation(): Company
    {
        $companyMock = $this->createMock(Company::class);

        $companyMock
            ->method('getContact')
            ->willReturn($this->getContactWithEmptyLocation());

        return $companyMock;
    }

    private function getContactWithEmptyLocation(): Contact
    {
        $contactMock = $this->createMock(Contact::class);

        $contactMock
            ->method('getFirstLocation')
            ->willReturn(null);

        return $contactMock;
    }
}

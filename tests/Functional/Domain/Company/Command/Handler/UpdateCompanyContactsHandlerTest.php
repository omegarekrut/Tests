<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Company\Collection\UrlAddressCollection;
use App\Domain\Company\Command\UpdateCompanyContactsCommand;
use App\Domain\Company\Command\UpdateCompanyContactsCommandFactory;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Location;
use App\Domain\Company\Entity\Phone;
use App\Domain\Company\Entity\ValueObject\UrlAddress;
use Doctrine\Common\Collections\Collection;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Company\Contact\LoadAquaMotorcycleShopsContact;
use Tests\Functional\TestCase;

/**
 * @group update-company
 */
class UpdateCompanyContactsHandlerTest extends TestCase
{
    private const NEW_COORDINATES_LATITUDE = 89.999999;
    private const NEW_COORDINATES_LONGITUDE = 179.999999;
    private const NEW_LOCATION = [
        'shopName' => 'Shop',
        'address' => 'new address',
        'schedule' => 'new schedule',
        'howToFind' => 'new how-to-find',
    ];

    private const NEW_SITES = ['https://new-site.com'];
    private const NEW_WHATSAPP = '+7 (999) 999-99-99';
    private const NEW_EMAIL = 'new.email@domain.com';
    private const NEW_PHONES = [
        [
            'phoneNumber' => self::NEW_WHATSAPP,
            'comment' => 'new phone number, same as whatsApp number',
        ],
    ];
    private const NEW_GOODS_DELIVERY = true;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
            LoadAquaMotorcycleShopsContact::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->company);

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $command = $this->fillCommandWithNewData(UpdateCompanyContactsCommandFactory::create($this->company));

        $this->getCommandBus()->handle($command);

        $updatedContact = $this->company->getContact();

        $this->assertSitesAreValid(self::NEW_SITES, $updatedContact->getSites());
        $this->assertWhatsAppIsValid(self::NEW_WHATSAPP, $updatedContact->getWhatsapp());
        $this->assertEmailIsValid(self::NEW_EMAIL, $updatedContact->getEmail());
        $this->assertPhonesAreValid(self::NEW_PHONES, $updatedContact->getPhones());
        $this->assertLocationIsValid(
            array_merge(self::NEW_LOCATION, [
                'coordinates' => [
                    'latitude' => self::NEW_COORDINATES_LATITUDE,
                    'longitude' => self::NEW_COORDINATES_LONGITUDE,
                ],
            ]),
            // TODO https://resolventa.atlassian.net/browse/FS-3079
            // Временное решение, настроить для работы со всеми адресами из коллекции
            $updatedContact->getLocations()->first()
        );
        $this->assertGoodsDeliveryAreValid(self::NEW_GOODS_DELIVERY, $updatedContact->isDeliveryToRegionsAvailable());
    }

    private function fillCommandWithNewData(UpdateCompanyContactsCommand $command): UpdateCompanyContactsCommand
    {
        $command->sites = self::NEW_SITES;
        $command->whatsapp = self::NEW_WHATSAPP;
        $command->email = self::NEW_EMAIL;
        $command->phones = self::NEW_PHONES;
        $command->locations = [
            array_merge(
                self::NEW_LOCATION,
                ['coordinates' => sprintf('%s,%s', self::NEW_COORDINATES_LATITUDE, self::NEW_COORDINATES_LONGITUDE)],
            ),
        ];
        $command->isDeliveryToRegionsAvailable = self::NEW_GOODS_DELIVERY;

        return $command;
    }

    /**
     * @param string[] $expectedSiteUrls
     */
    private function assertSitesAreValid(array $expectedSiteUrls, UrlAddressCollection $sites): void
    {
        $this->assertCount(count($expectedSiteUrls), $sites);

        /** @var UrlAddress $site */
        foreach ($sites as $site) {
            $this->assertContains((string) $site, $expectedSiteUrls);
        }
    }

    private function assertWhatsAppIsValid(string $expectedWhatsApp, ?string $whatsApp): void
    {
        $this->assertEquals($expectedWhatsApp, $whatsApp);
    }

    private function assertEmailIsValid(string $expectedEmail, ?string $email): void
    {
        $this->assertEquals($expectedEmail, $email);
    }

    /**
     * @param string[] $expectedPhones
     */
    private function assertPhonesAreValid(array $expectedPhones, Collection $phones): void
    {
        $this->assertCount(count($expectedPhones), $phones);

        /** @var Phone $phone */
        foreach ($phones as $phone) {
            $this->assertContains($phone->getPhoneNumber(), array_column($expectedPhones, 'phoneNumber'));
            $this->assertContains($phone->getComment(), array_column($expectedPhones, 'comment'));
        }
    }

    /**
     * @param string[] $expectedLocation
     */
    private function assertLocationIsValid(array $expectedLocation, ?Location $location): void
    {
        $this->assertNotNull($location);

        $this->assertEquals($expectedLocation['address'], $location->getAddress());
        $this->assertEquals($expectedLocation['schedule'], $location->getSchedule());
        $this->assertEquals($expectedLocation['howToFind'], $location->getHowToFind());

        $this->assertEquals($expectedLocation['coordinates']['latitude'], $location->getCoordinates()->getLatitude());
        $this->assertEquals($expectedLocation['coordinates']['longitude'], $location->getCoordinates()->getLongitude());
    }

    private function assertGoodsDeliveryAreValid(bool $expectedGoodsDelivery, bool $goodsDelivery): void
    {
        $this->assertEquals($expectedGoodsDelivery, $goodsDelivery);
    }
}

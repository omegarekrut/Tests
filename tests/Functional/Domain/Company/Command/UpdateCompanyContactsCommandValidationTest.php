<?php

namespace Tests\Functional\Domain\Company\Command;

use App\Domain\Company\Command\UpdateCompanyContactsCommand;
use App\Domain\Company\Command\UpdateCompanyContactsCommandFactory;
use App\Domain\Company\Entity\Company;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Company\Contact\LoadAquaMotorcycleShopsContact;
use Tests\Functional\ValidationTestCase;

/**
 * @group update-company
 */
class UpdateCompanyContactsCommandValidationTest extends ValidationTestCase
{
    private UpdateCompanyContactsCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
            LoadAquaMotorcycleShopsContact::class,
        ])->getReferenceRepository();

        /** @var Company $company */
        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);

        $this->command = UpdateCompanyContactsCommandFactory::create($company);
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testArrayFieldsRequiredKeys(): void
    {
        $this->command->phones = [[]];
        $this->command->locations = [[]];

        $errors = $this->getValidator()->validate($this->command);

        $this->assertCount(7, $errors);
    }

    public function testInvalidLength(): void
    {
        $tooLongText = $this->getFaker()->realText(500);

        $this->command->phones = [
            [
                'phoneNumber' => '+7 (999) 999-99-99',
                'comment' => $tooLongText,
            ],
        ];
        $this->command->locations = [
            [
                'shopName' => $tooLongText,
                'address' => $tooLongText,
                'schedule' => $tooLongText,
                'howToFind' => $tooLongText,
                'coordinates' => '82.929633,55.049404',
            ],
        ];

        $this->getValidator()->validate($this->command);

        $errors = $this->getValidator()->getLastErrors();

        $this->assertCount(5, $errors);

        foreach ($errors as $error) {
            $this->assertEquals('Максимальная длина поля 255 символов.', $error->getMessage());
        }
    }

    public function testInvalidPhoneNumber(): void
    {
        $this->command->whatsapp = 'clearly not a phone number';
        $this->command->phones = [
            [
                'phoneNumber' => 'clearly not a phone number',
                'comment' => null,
            ],
        ];

        $errors = $this->getValidator()->validate($this->command);

        $this->assertCount(2, $errors);

        foreach ($errors as $error) {
            $this->assertEquals(
                'Номер телефона должен соответствовать формату маски. Пример: +7 (000) 000-00-00',
                $error->getMessage(),
            );
        }
    }

    public function testInvalidCoordinatesMessage(): void
    {
        $this->command->locations = [
            [
                'shopName' => null,
                'address' => null,
                'schedule' => null,
                'howToFind' => null,
                'coordinates' => 'clearly not coordinates',
            ],
        ];

        $this->getValidator()->validate($this->command);

        $errors = $this->getValidator()->getLastErrors();

        $this->assertCount(1, $errors);
        $this->assertEquals(
            'Координаты должны быть представлены в виде \'широта,долгота\'.',
            $errors[0]->getMessage()
        );
    }

    public function testInvalidSitesWithoutScheme(): void
    {
        $siteUrl = 'fishingib.ru';

        $this->command->sites = [$siteUrl];

        $this->getValidator()->validate($this->command);

        $errors = $this->getValidator()->getLastErrors();

        $this->assertCount(1, $errors);
        $this->assertEquals(
            'Адрес сайта должен начинаться с https:// или http://',
            $errors[0]->getMessage()
        );
    }

    public function testInvalidSites(): void
    {
        $siteUrl = 'https://fishingib';

        $this->command->sites = [$siteUrl];

        $this->getValidator()->validate($this->command);

        $errors = $this->getValidator()->getLastErrors();

        $this->assertCount(1, $errors);
        $this->assertEquals(
            sprintf('%s не является корректным адресом сайта', $siteUrl),
            $errors[0]->getMessage()
        );
    }

    public function testValid(): void
    {
        $this->getValidator()->validate($this->command);

        $errors = $this->getValidator()->getLastErrors();

        $this->assertCount(0, $errors);
    }

    /** @dataProvider validCoordinateProvider */
    public function testValidCoordinates(?string $validCoordinate): void
    {
        $this->command->locations = [
            [
                'shopName' => null,
                'address' => 'Москва',
                'schedule' => null,
                'howToFind' => null,
                'coordinates' => $validCoordinate,
            ],
        ];

        $violations = $this->getValidator()->validate($this->command);

        $this->assertEmpty($violations);
    }

    public function validCoordinateProvider(): \Generator
    {
        yield ['25.01,58.05'];

        yield ['25.01,58.05'];

        yield ['89.01,58.05'];

        yield ['0,0'];

        yield ['-5,-5'];
    }

    /** @dataProvider invalidCoordinateProvider */
    public function testInvalidCoordinates(?string $invalidCoordinates): void
    {
        $this->command->locations = [
            [
                'address' => 'Москва',
                'schedule' => null,
                'howToFind' => null,
                'coordinates' => $invalidCoordinates,
            ],
        ];

        $violations = $this->getValidator()->validate($this->command);

        $this->assertNotEmpty($violations);
    }

    public function invalidCoordinateProvider(): \Generator
    {
        yield ['25, 25'];

        yield [' 25, 25'];

        yield [' 25, 25'];

        yield [' 2  5,    25'];

        yield ['ahaha'];

        yield [''];

        yield [',25'];

        yield [','];

        yield ['25,'];

        yield [null];
    }

    public function testEmptyLocationValid(): void
    {
        $this->command->location = null;

        $violations = $this->getValidator()->validate($this->command);

        $this->assertEmpty($violations);
    }
}

<?php

namespace Tests\Functional\Domain\BusinessSubscription\Command;

use App\Domain\BusinessSubscription\Command\UpdateCompanySubscriptionCommand;
use App\Domain\BusinessSubscription\Entity\CompanySubscription;
use App\Domain\BusinessSubscription\Entity\ValueObject\TariffsType;
use App\Domain\Company\Entity\Company;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\CompanyWithSubscription\LoadCompanyWithActiveSubscription;
use Tests\Functional\ValidationTestCase;

/**
 * @group business_subscription
 */
class UpdateCompanySubscriptionCommandValidationTest extends ValidationTestCase
{
    private UpdateCompanySubscriptionCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $company = $this->createMock(Company::class);
        $company
            ->method('getId')
            ->willReturn(Uuid::uuid4());

        $companySubscription = new CompanySubscription(Uuid::uuid4(), TariffsType::standard(), new DateTimeImmutable(), new DateTimeImmutable());
        $companySubscription = $companySubscription->withCompany($company);

        $this->command = new UpdateCompanySubscriptionCommand($companySubscription);
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testValidCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithActiveSubscription::class,
        ])->getReferenceRepository();

        $companyWithSubscription = $referenceRepository->getReference(LoadCompanyWithActiveSubscription::REFERENCE_NAME);
        assert($companyWithSubscription instanceof Company);

        $command = new UpdateCompanySubscriptionCommand($companyWithSubscription->getSubscriptions()->first());

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }

    public function testNotExistsCompanyField(): void
    {
        $this->command->companyId = null;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('companyId', 'Компания не найдена.');
    }

    public function testInvalidTariffField(): void
    {
        $this->command->tariff = TariffsType::base();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('tariff', 'Выбран некорректный тариф.');
    }

    public function testRequiredFields(): void
    {
        $this->command->tariff = null;
        $this->command->expiredAt = null;
        $this->command->startedAt = null;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('tariff', 'Это поле обязательно для заполнения');
        $this->assertFieldInvalid('startedAt', 'Это поле обязательно для заполнения');
        $this->assertFieldInvalid('expiredAt', 'Это поле обязательно для заполнения');
    }

    public function testExpiredAtShouldBeGreaterThanStartedFields(): void
    {
        $date = new DateTime('2023-01-01 00:00:00', new DateTimeZone('Asia/Novosibirsk'));
        $this->command->startedAt = $date;
        $this->command->expiredAt = $date->sub(new DateInterval('P1D'));

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('startedAt', 'Значение должно быть меньше чем Dec 31, 2022, 12:00 AM.');
    }

    public function testStartedAtShouldBeLessThanExpiredFields(): void
    {
        $date = new DateTime('2023-01-01 00:00:00', new DateTimeZone('Asia/Novosibirsk'));
        $this->command->startedAt = $date->add(new DateInterval('P1D'));
        $this->command->expiredAt = $date;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('expiredAt', 'Значение должно быть больше чем Jan 2, 2023, 12:00 AM.');
    }
}

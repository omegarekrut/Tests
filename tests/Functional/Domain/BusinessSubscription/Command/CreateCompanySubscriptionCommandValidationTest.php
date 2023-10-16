<?php

namespace Tests\Functional\Domain\BusinessSubscription\Command;

use App\Domain\BusinessSubscription\Command\CreateCompanySubscriptionCommand;
use App\Domain\BusinessSubscription\Entity\ValueObject\TariffsType;
use App\Domain\Company\Entity\Company;
use DateInterval;
use DateTime;
use DateTimeZone;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwner;
use Tests\Functional\ValidationTestCase;

/**
 * @group business_subscription
 */
class CreateCompanySubscriptionCommandValidationTest extends ValidationTestCase
{
    private CreateCompanySubscriptionCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithoutOwner::class,
        ])->getReferenceRepository();

        $companyWithSubscription = $referenceRepository->getReference(LoadCompanyWithoutOwner::REFERENCE_NAME);
        assert($companyWithSubscription instanceof Company);

        $this->command = new CreateCompanySubscriptionCommand(Uuid::uuid4(), $companyWithSubscription);
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testValidCommand(): void
    {
        $this->command->tariff = TariffsType::standard();
        $this->command->startedAt = new DateTime('2023-01-01 00:00:00', new DateTimeZone('Asia/Novosibirsk'));
        $this->command->expiredAt = new DateTime('2023-01-02 00:00:00', new DateTimeZone('Asia/Novosibirsk'));
        $this->command->comment = 'comment';
        $this->command->externalPaymentId = 'external-payment-id';

        $this->getValidator()->validate($this->command);

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

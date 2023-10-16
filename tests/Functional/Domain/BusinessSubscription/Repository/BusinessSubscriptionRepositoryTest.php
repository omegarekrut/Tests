<?php

namespace Tests\Functional\Domain\BusinessSubscription\Repository;

use App\Domain\BusinessSubscription\Entity\Benefit;
use App\Domain\BusinessSubscription\Entity\Tariff;
use App\Domain\BusinessSubscription\Entity\ValueObject\BenefitsType;
use App\Domain\BusinessSubscription\Entity\ValueObject\Price;
use App\Domain\BusinessSubscription\Entity\ValueObject\TariffRestrictions;
use App\Domain\BusinessSubscription\Entity\ValueObject\TariffsType;
use App\Domain\BusinessSubscription\Exception\TariffNoExistException;
use App\Domain\BusinessSubscription\Repository\BusinessSubscriptionRepository;
use App\Domain\Company\Entity\Company;
use Doctrine\Common\Collections\ArrayCollection;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Company\CompanyWithSubscription\LoadCompanyWithActiveSubscription;
use Tests\Functional\TestCase;

/**
 * @group business_subscription
 */
class BusinessSubscriptionRepositoryTest extends TestCase
{
    /**
     * @throws TariffNoExistException
     */
    public function testGetTariffByName(): void
    {
        $baseTariff = $this->createBaseTariff();
        $businessSubscriptionRepository = $this->createBusinessRepositoryWithTariff($baseTariff);

        $exceptedTariff = $baseTariff;
        $exceptedTariffType = $baseTariff->getType();
        $exceptedTariffName = $baseTariff->getName();
        $exceptedTariffPrice = $baseTariff->getPrice();
        $exceptedPeriodBetweenPublicationInDays = $baseTariff->getRestrictions()->getPeriodBetweenPublicationsInDays();
        $exceptedTariffBenefits = $baseTariff->getBenefits();

        $tariff = $businessSubscriptionRepository->getTariffByType(TariffsType::base());

        $this->assertEquals($exceptedTariff, $tariff);
        $this->assertEquals($exceptedTariffType, $tariff->getType());
        $this->assertEquals($exceptedTariffName, $tariff->getName());
        $this->assertEquals($exceptedTariffPrice, $tariff->getPrice());
        $this->assertEquals($exceptedPeriodBetweenPublicationInDays, $tariff->getRestrictions()->getPeriodBetweenPublicationsInDays());
        $this->assertEquals($exceptedTariffBenefits, $tariff->getBenefits());
    }

    /**
     * @throws TariffNoExistException
     */
    public function testGetBenefitsFormTariff(): void
    {
        $baseTariff = $this->createBaseTariff();
        $businessSubscriptionRepository = $this->createBusinessRepositoryWithTariff($baseTariff);
        $exceptedBenefitsTariff = $baseTariff->getBenefits();

        $tariffBenefits = $businessSubscriptionRepository->getBenefitsFromTariff(TariffsType::base());

        $this->assertEquals($exceptedBenefitsTariff, $tariffBenefits);
    }

    public function testGetTariffByNameThatDoesNotExistShouldBeException(): void
    {
        $baseTariff = $this->createBaseTariff();
        $businessSubscriptionRepository = $this->createBusinessRepositoryWithTariff($baseTariff);

        $this->expectException(TariffNoExistException::class);
        $this->expectExceptionMessage('Tariff with this type was not found.');

        $businessSubscriptionRepository->getTariffByType(TariffsType::standard());
    }

    public function testIsContainsBenefitInTariff(): void
    {
        $baseTariff = $this->createBaseTariff();
        $businessSubscriptionRepository = $this->createBusinessRepositoryWithTariff($baseTariff);

        $isContainBenefit = $businessSubscriptionRepository->isContainsBenefitInTariff($baseTariff, BenefitsType::cardCompany());

        $this->assertTrue($isContainBenefit);
    }

    public function testGetTariffOfCompanyWithStandardSubscription(): void
    {
        $this->clearDatabase();
        $company = $this->loadFixture(LoadCompanyWithActiveSubscription::class, Company::class);

        $standardTariff = $this->createStandardTariff();
        $businessSubscriptionRepository = $this->createBusinessRepositoryWithTariff($standardTariff);

        $tariff = $businessSubscriptionRepository->getTariffOfCompany($company);

        $this->assertEquals($standardTariff->getType(), $tariff->getType());
    }

    public function testGetTariffOfCompanyWithoutSubscription(): void
    {
        $this->clearDatabase();
        $company = $this->loadFixture(LoadCompanyWithOwner::class, Company::class);

        $baseTariff = $this->createBaseTariff();
        $businessSubscriptionRepository = $this->createBusinessRepositoryWithTariff($baseTariff);

        $tariff = $businessSubscriptionRepository->getTariffOfCompany($company);

        $this->assertEquals($baseTariff->getType(), $tariff->getType());
    }

    private function createBusinessRepositoryWithTariff(Tariff $tariff): BusinessSubscriptionRepository
    {
        return new BusinessSubscriptionRepository([$tariff]);
    }

    private function createBaseTariff(): Tariff
    {
        $benefits = new ArrayCollection();
        $benefits->add(new Benefit(BenefitsType::cardCompany()));
        $benefits->add(new Benefit(BenefitsType::newsCompany()));

        $baseTariffRestrictions = new TariffRestrictions(true, true, 30);

        return new Tariff(TariffsType::base(), 'Базовый', new Price(0), $baseTariffRestrictions, $benefits);
    }

    private function createStandardTariff(): Tariff
    {
        $benefits = new ArrayCollection();
        $standardTariffRestrictions = new TariffRestrictions(false, false, 7);

        return new Tariff(TariffsType::standard(), 'Стандарт', new Price(1000), $standardTariffRestrictions, $benefits);
    }
}

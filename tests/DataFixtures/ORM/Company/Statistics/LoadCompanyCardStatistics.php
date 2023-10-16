<?php

namespace Tests\DataFixtures\ORM\Company\Statistics;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Statistics\CompanyCardStatistics;
use App\Domain\Company\Entity\Statistics\ValueObject\StatisticsType;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadSetOfSimilarCompanies;

class LoadCompanyCardStatistics extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_COMPANY_CARD_STATISTICS_PHONE = 'statistics-contact-phone-number-views-for-original-company';
    public const REFERENCE_COMPANY_CARD_STATISTICS_WHATSAPP = 'statistics-contact-whatsapp-number-views-for-original-company';

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadSetOfSimilarCompanies::ORIGINAL_COMPANY_REFERENCE);
        assert($company instanceof Company);

        $companyPhoneViewingStatistics = new CompanyCardStatistics(
            Uuid::uuid4(),
            $company,
            Carbon::today(),
            StatisticsType::phoneViews(),
        );

        $this->addReference(self::REFERENCE_COMPANY_CARD_STATISTICS_PHONE, $companyPhoneViewingStatistics);

        $manager->persist($companyPhoneViewingStatistics);

        $companyWhatsappViewingStatistics = new CompanyCardStatistics(
            Uuid::uuid4(),
            $company,
            Carbon::today(),
            StatisticsType::whatsappViews(),
        );

        $this->addReference(self::REFERENCE_COMPANY_CARD_STATISTICS_WHATSAPP, $companyWhatsappViewingStatistics);

        $manager->persist($companyWhatsappViewingStatistics);

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadSetOfSimilarCompanies::class,
        ];
    }
}

<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\ValueObject\BackgroundImage;
use App\Util\ImageStorage\ValueObject\ImageCroppingParameters;
use App\Domain\Company\Entity\ValueObject\LogoImage;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\Company\Rubric\LoadPaidReservoirsRubric;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadPaidReservoirsCompany extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'paid-reservoirs-company';

    private Generator $generator;
    private CompanyFactory $companyFactory;
    private MediaHelper $mediaHelper;

    public function __construct(
        Generator $generator,
        CompanyFactory $companyFactory,
        MediaHelper $mediaHelper
    ) {
        $this->generator = $generator;
        $this->companyFactory = $companyFactory;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var User $userTest */
        $userTest = $this->getReference(LoadTestUser::USER_TEST);

        $company = $this->createCompany();

        $company->setOwner($userTest);
        $company->updateDescription($this->generator->realText());
        $company->setLogoImage(new LogoImage($this->mediaHelper->createImage(), new ImageCroppingParameters(50, 50, 100, 100)));
        $company->setBackgroundImage(new BackgroundImage($this->mediaHelper->createImage(), new ImageCroppingParameters(100, 100, 100, 100)));

        $manager->persist($company);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $company);
    }

    private function createCompany(): Company
    {
        $name = 'Изумрудный';
        $rubric = $this->getReference(LoadPaidReservoirsRubric::REFERENCE_NAME);
        $rubrics = new ArrayCollection([$rubric]);

        return $this->companyFactory->createCompany($name, $rubrics);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadPaidReservoirsRubric::class,
            LoadTestUser::class,
        ];
    }
}

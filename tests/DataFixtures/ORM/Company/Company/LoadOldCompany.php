<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Collection\LocationCollection;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Location;
use App\Domain\Company\Entity\ValueObject\BackgroundImage;
use App\Domain\Company\Entity\ValueObject\LogoImage;
use App\Domain\Company\Exception\RubricsIsEmptyException;
use App\Domain\Region\Entity\Region;
use App\Domain\User\Entity\User;
use App\Util\Coordinates\Coordinates;
use App\Util\ImageStorage\ValueObject\ImageCroppingParameters;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\Helper\Factory\ContactDTOFakeFactory;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\Company\Rubric\LoadAquaMotorcycleShopsRubric;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadOldCompany extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'old-company';

    private ContactDTOFakeFactory $contactDTOFakeFactory;
    private CompanyFactory $companyFactory;
    private MediaHelper $mediaHelper;

    public function __construct(ContactDTOFakeFactory $contactDTOFakeFactory, CompanyFactory $companyFactory, MediaHelper $mediaHelper)
    {
        $this->contactDTOFakeFactory = $contactDTOFakeFactory;
        $this->companyFactory = $companyFactory;
        $this->mediaHelper = $mediaHelper;
    }

    /**
     * @throws RubricsIsEmptyException
     */
    public function load(ObjectManager $manager): void
    {
        Carbon::setTestNow(Carbon::now()->subYears(2));

        $userTest = $this->getReference(LoadTestUser::USER_TEST);
        assert($userTest instanceof User);

        $region = $this->getReference(LoadTestRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $location = new Location(Uuid::uuid4(), new Coordinates(40.71427, -74.00597));
        $location->setRegion($region);

        $contactDTO = $this->contactDTOFakeFactory->createFakeContactDTO();
        $contactDTO->locations = new LocationCollection([$location]);

        $company = $this->createCompany();
        $company->rewriteContactsFromDTO($contactDTO);

        $company->setOwner($userTest);
        $company->updateDescription('Базовое описание компании');
        $company->editBasicInfo(
            $company->getName(),
            $company->getSlug(),
            'Краткое описание компании',
            $company->getRubrics()
        );
        $company->setLogoImage(new LogoImage($this->mediaHelper->createImage(), new ImageCroppingParameters(50, 50, 100, 100)));
        $company->setBackgroundImage(new BackgroundImage($this->mediaHelper->createImage(), new ImageCroppingParameters(100, 100, 100, 100)));

        $company->rewriteImages(new ArrayCollection([$this->mediaHelper->createImage(), $this->mediaHelper->createImage()]));
        $company->rewriteVideos(new ArrayCollection([$this->mediaHelper->createVideo()]));

        $manager->persist($company);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $company);

        Carbon::setTestNow();
    }

    private function createCompany(): Company
    {
        $name = 'Компания, которую не обновляли более двух лет';
        $rubric = $this->getReference(LoadAquaMotorcycleShopsRubric::REFERENCE_NAME);
        $rubrics = new ArrayCollection([$rubric]);

        return $this->companyFactory->createCompany($name, $rubrics);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadTestRegion::class,
            LoadAquaMotorcycleShopsRubric::class,
            LoadTestUser::class,
        ];
    }
}

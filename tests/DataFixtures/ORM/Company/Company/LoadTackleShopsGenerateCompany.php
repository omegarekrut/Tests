<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\ValueObject\BackgroundImage;
use App\Module\SlugGenerator\SlugGenerator;
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
use Tests\DataFixtures\ORM\Company\Rubric\LoadTackleShopsRubric;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadTackleShopsGenerateCompany extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_PREFIX = 'tackle-shops-generate-company';
    private const COUNT = 20;

    private Generator $generator;
    private CompanyFactory $companyFactory;
    private MediaHelper $mediaHelper;
    private SlugGenerator $slugGenerator;

    public function __construct(
        Generator $generator,
        CompanyFactory $companyFactory,
        MediaHelper $mediaHelper,
        SlugGenerator $slugGenerator
    ) {
        $this->generator = $generator;
        $this->companyFactory = $companyFactory;
        $this->mediaHelper = $mediaHelper;
        $this->slugGenerator = $slugGenerator;
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', self::REFERENCE_PREFIX, rand(1, self::COUNT));
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadDefault($manager);
        $manager->flush();
    }

    public function loadDefault(ObjectManager $manager): void
    {
        /** @var User $userTest */
        $userTest = $this->getReference(LoadTestUser::USER_TEST);

        for ($i = 1; $i <= self::COUNT; $i++) {
            $company = $this->createCompany($i);

            $company->setOwner($userTest);
            $company->updateDescription($this->generator->realText());
            $company->setLogoImage(new LogoImage($this->mediaHelper->createImage(), new ImageCroppingParameters(50, 50, 100, 100)));
            $company->setBackgroundImage(new BackgroundImage($this->mediaHelper->createImage(), new ImageCroppingParameters(100, 100, 100, 100)));

            $manager->persist($company);

            $this->addReference(sprintf('%s-%d', self::REFERENCE_PREFIX, $i), $company);
        }
    }

    private function createCompany(int $slugSuffix): Company
    {
        $name = $this->generator->company;
        $rubric = $this->getReference(LoadTackleShopsRubric::REFERENCE_NAME);
        $rubrics = new ArrayCollection([$rubric]);

        $company = $this->companyFactory->createCompany($name, $rubrics);

        $slug = sprintf('%s-%s', $this->slugGenerator->generate($name, Company::class), $slugSuffix);
        $scopeActivity = $this->generator->catchPhrase;
        $company->editBasicInfo($name, $slug, $scopeActivity, $rubrics);

        return $company;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadTackleShopsRubric::class,
            LoadTestUser::class,
        ];
    }
}

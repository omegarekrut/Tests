<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use stdClass;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadDefaultRubric;

class LoadCompanyWithSocialLinks extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'company-with-social-links';

    private CompanyFactory $companyFactory;

    public function __construct(CompanyFactory $companyFactory)
    {
        $this->companyFactory = $companyFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->createCompany();
        $socialLinks = $this->createSocialLinks();

        $company->rewriteSocialLinksFromDTO($socialLinks);

        $manager->persist($company);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $company);
    }

    private function createCompany(): Company
    {
        $name = self::REFERENCE_NAME;
        $rubric = $this->getReference(LoadDefaultRubric::REFERENCE_NAME);
        $rubrics = new ArrayCollection([$rubric]);

        return $this->companyFactory->createCompany($name, $rubrics);
    }

    private function createSocialLinks(): stdClass
    {
        $socialLinks = new stdClass();

        $socialLinks->youtube = 'https://www.youtube.com/channel/ChannelName';
        $socialLinks->vk = 'https://vk.com/UserName';
        $socialLinks->ok = 'https://ok.ru/profile/UserName';
        $socialLinks->instagram = 'https://www.instagram.com/UserName';
        $socialLinks->facebook = 'https://www.facebook.com/UserName';
        $socialLinks->telegram = 'https://t.me/UserName';
        $socialLinks->yandexZen = 'https://zen.yandex.ru/Name';

        return $socialLinks;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadDefaultRubric::class,
        ];
    }
}

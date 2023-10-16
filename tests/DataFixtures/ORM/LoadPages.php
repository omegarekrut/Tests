<?php

namespace Tests\DataFixtures\ORM;

use App\Domain\Page\Entity\Metadata;
use App\Domain\Page\Entity\Page;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadPages extends Fixture implements DependentFixtureInterface
{
    private const REFERENCE_PREFIX = 'page';

    public const PAGE_ABOUT = 'about';
    public const PAGE_CONTACTS = 'contacts';
    public const PAGE_RULES = 'rules';
    public const PAGE_SOUV = 'souv';
    public const PAGE_SEARCH = 'search';
    public const PAGE_BUSINESS_ACCOUNT = 'business-account';
    public const PAGE_BUSINESS_FAQ = 'business-faq';
    private AuthorHelper $authorHelper;
    private \Faker\Generator $generator;

    public function __construct(AuthorHelper $authorHelper, \Faker\Generator $generator)
    {
        $this->authorHelper = $authorHelper;
        $this->generator = $generator;
    }

    public static function getReferenceName($slug): string
    {
        return sprintf('%s-%s', self::REFERENCE_PREFIX, $slug);
    }

    public function load(ObjectManager $manager): void
    {
        /** @var User $testUser */
        $testUser = $this->getReference(LoadTestUser::USER_TEST);

        foreach ($this->getPageConfig() as $pageConfig) {
            [$slug, $title] = $pageConfig;

            $page = new Page(
                $slug,
                $title,
                $this->generator->randomHtml(),
                $this->authorHelper->createFromUser($testUser),
                new Metadata($this->generator->realText(100), $this->generator->realText(100))
            );

            $manager->persist($page);
            $this->addReference(sprintf('%s-%s', self::REFERENCE_PREFIX, $slug), $page);
        }

        $manager->flush();
    }

    private function getPageConfig(): \Generator
    {
        yield [self::PAGE_ABOUT, 'О сайте Новосибирских рыбаков'];

        yield [self::PAGE_CONTACTS, 'Контакты'];

        yield [self::PAGE_RULES, 'Правила пользования сайтом'];

        yield [self::PAGE_SOUV, 'Сувениры FishingSib: кепки, наклейки на машины, флаги'];

        yield [self::PAGE_SEARCH, 'Поиск по сайту'];

        yield [self::PAGE_BUSINESS_ACCOUNT, 'Корпоративный аккаунт на FishingSib: новые клиенты для вашего бизнеса'];

        yield [self::PAGE_BUSINESS_FAQ, 'Правила ведения бизнесс-аккаунта на FishingSib'];
    }

    public function getDependencies(): array
    {
        return [
            LoadTestUser::class,
        ];
    }
}

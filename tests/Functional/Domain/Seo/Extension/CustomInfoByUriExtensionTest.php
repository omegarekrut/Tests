<?php

namespace Tests\Functional\Domain\Seo\Extension;

use App\Domain\Seo\Entity\SeoData;
use App\Domain\Seo\Extension\CustomInfoByUriExtension;
use App\Domain\Seo\Helper\SeoPropertyTemplateRenderHelper;
use App\Domain\Seo\Repository\SeoDataRepository;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\DataFixtures\ORM\Seo\LoadSeoData;
use Tests\Functional\RepositoryTestCase;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class CustomInfoByUriExtensionTest extends RepositoryTestCase
{
    private $seoPage;
    private $seoCustomInfoByUriExtension;

    /**
     * @var SeoDataRepository
     */
    private $seoDataRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([LoadSeoData::class,]);

        $this->seoDataRepository = $this->getRepository(SeoData::class);
        $this->seoPage = $this->createSeoPage();
    }

    protected function tearDown(): void
    {
        unset(
            $this->seoDataRepository,
            $this->seoPage
        );

        parent::tearDown();
    }

    /**
     * @dataProvider seoData
     */
    public function testApplySeoData(string $uri, string $expectedTitle, string $expectedDescription, string $expectedH1): void
    {
        $this->seoCustomInfoByUriExtension = new CustomInfoByUriExtension(
            $this->seoDataRepository,
            new Request($uri),
            new SeoPropertyTemplateRenderHelper()
        );
        $this->seoCustomInfoByUriExtension
            ->apply($this->seoPage, new SeoContext([]));

        $this->assertEquals($expectedTitle, $this->seoPage->getTitle());
        $this->assertEquals($expectedDescription, $this->seoPage->getDescription());
        $this->assertEquals($expectedH1, $this->seoPage->getH1());
    }

    public function seoData(): array
    {
        $month = \IntlDateFormatter::formatObject(new \DateTime(), 'LLLL', 'ru_RU');
        $year = date('Y');

        return [
            LoadSeoData::GALLERY => [
                'uri' => '/gallery/',
                'title' => 'Title Рыболовная фотогалерея',
                'description' => 'Description Рыболовная фотогалерея',
                'h1' => 'H1 Рыболовная фотогалерея',
            ],
            LoadSeoData::TACKLE => [
                'uri' => '/tackles/',
                'title' => 'Отзывы о снастях',
                'description' => 'Отзывы о снастях',
                'h1' => 'Отзывы о снастях',
            ],
            LoadSeoData::HUMAN_PATTERN => [
                'uri' => '/articles/view/1234/',
                'title' => 'Шаблон * Title `SeoPage Title`',
                'description' => 'Шаблон * Description `SeoPage Description`',
                'h1' => sprintf('Шаблон * H1 `SeoPage H1`, Month `%s`, Year `%d`', $month, $year),
            ],
            LoadSeoData::REGEX_PATTERN => [
                'uri' => '/articles/',
                'title' => 'Шаблон # Title `SeoPage Title`',
                'description' => 'Шаблон # Description `SeoPage Description`',
                'h1' => sprintf('Шаблон # H1 `SeoPage H1`, Month `%s`, Year `%d`', $month, $year),
            ],
            LoadSeoData::WITH_QUERY_STRING => [
                'uri' => '/tackles/?'.http_build_query(['aaa' => 'bbb', 'search' => 'рыбалка',]),
                'title' => 'Шаблон _GET & * Title `SeoPage Title`',
                'description' => 'Шаблон _GET & * Description `SeoPage Description`',
                'h1' => sprintf('Шаблон _GET & *, Search `рыбалка`, Month `%s`, Year `%d`', $month, $year),
            ],
            'not matched uri' => [
                'uri' => '/aaa/bbbb/cccc/',
                'title' => 'SeoPage Title',
                'description' => 'SeoPage Description',
                'h1' => 'SeoPage H1',
            ],
        ];
    }

    public function testWithUriMethod(): void
    {
        $this->seoCustomInfoByUriExtension = new CustomInfoByUriExtension(
            $this->seoDataRepository,
            new Request('/articles/'),
            new SeoPropertyTemplateRenderHelper()
        );

        $this->seoCustomInfoByUriExtension
            ->apply($this->seoPage, new SeoContext([]));

        $originalSeoPage = clone $this->seoPage;

        $this->seoCustomInfoByUriExtension
            ->withUri(new Uri('/gallery/'))
            ->apply($this->seoPage, new SeoContext([]));

        $this->assertNotEquals($originalSeoPage->getTitle(), $this->seoPage->getTitle());
        $this->assertNotEquals($originalSeoPage->getDescription(), $this->seoPage->getDescription());
        $this->assertNotEquals($originalSeoPage->getH1(), $this->seoPage->getH1());
    }

    private function createSeoPage(): SeoPage
    {
        $seoPage = new SeoPage();
        $seoPage
            ->setTitle('SeoPage Title')
            ->setDescription('SeoPage Description')
            ->setH1('SeoPage H1');

        return $seoPage;
    }
}

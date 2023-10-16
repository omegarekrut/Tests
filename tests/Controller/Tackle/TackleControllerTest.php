<?php

namespace Tests\Controller\Tackle;

use App\Domain\Record\Tackle\Entity\Tackle;
use App\Domain\Record\Tackle\Entity\TackleBrand;
use App\Domain\Record\Tackle\Entity\TackleReview;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\LoadTackleBrands;
use Tests\DataFixtures\ORM\Record\LoadTackleReviews;
use Tests\DataFixtures\ORM\Record\LoadTackleWithoutReview;

class TackleControllerTest extends TestCase
{
    private const TACKLE_TITLE = 'Отзывы о снастях';
    private const TACKLE_REVIEW = 'Отзыв пользователя';

    private Tackle $tackle;
    private TackleBrand $brand;
    private TackleReview $review;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTackleWithoutReview::class,
            LoadTackleBrands::class,
            LoadTackleReviews::class,
        ])->getReferenceRepository();

        $tackle = $referenceRepository->getReference(LoadTackleWithoutReview::getRandReferenceName());
        assert($tackle instanceof Tackle);

        $brand = $referenceRepository->getReference(LoadTackleBrands::getRandReferenceName());
        assert($brand instanceof TackleBrand);

        $review = $referenceRepository->getReference(LoadTackleReviews::getRandReferenceName());
        assert($review instanceof TackleReview);

        $this->tackle = $tackle;
        $this->brand = $brand;
        $this->review = $review;
    }
    public function testAllowAccessOnTackleCategory(): void
    {
        $client = $this->getBrowser();
        $url = sprintf('/tackles/%s/', $this->tackle->getCategory()->getSlug());

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::TACKLE_REVIEW,
            $page->html()
        );
    }

    public function testAllowAccessOnTackleBrand(): void
    {
        $client = $this->getBrowser();
        $url = sprintf('/tackles/brand:%s/', $this->brand->getSlug());

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::TACKLE_TITLE,
            $page->html()
        );
    }

    public function testAllowAccessOnTackleReview(): void
    {
        $client = $this->getBrowser();
        $url = sprintf('/tackles/review/%d/', $this->review->getId());

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::TACKLE_REVIEW,
            $page->html()
        );
    }
}

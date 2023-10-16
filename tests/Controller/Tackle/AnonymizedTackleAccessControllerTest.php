<?php

namespace Tests\Controller\Tackle;

use App\Domain\Record\Tackle\Entity\Tackle;
use App\Domain\Record\Tackle\Entity\TackleReview;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\LoadAnonymizedTackleWithAnonymizedTackleReviewWhichHasAnonymizedComment;
use Symfony\Component\HttpFoundation\Response;

class AnonymizedTackleAccessControllerTest extends TestCase
{
    private Tackle $anonymizedTackle;
    private TackleReview $anonymizedTackleReview;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAnonymizedTackleWithAnonymizedTackleReviewWhichHasAnonymizedComment::class,
        ])->getReferenceRepository();

        $anonymizedTackle = $referenceRepository->getReference(LoadAnonymizedTackleWithAnonymizedTackleReviewWhichHasAnonymizedComment::ANONYMIZED_TACKLE);
        assert($anonymizedTackle instanceof Tackle);

        $anonymizedTackleReview = $referenceRepository->getReference(LoadAnonymizedTackleWithAnonymizedTackleReviewWhichHasAnonymizedComment::ANONYMIZED_TACKLE_REVIEW);
        assert($anonymizedTackleReview instanceof TackleReview);

        $this->anonymizedTackle = $anonymizedTackle;
        $this->anonymizedTackleReview = $anonymizedTackleReview;
    }

    public function testAllowForTackle(): void
    {
        $client = $this->getBrowser();

        $url = sprintf('/tackles/view/%d/', $this->anonymizedTackle->getId());

        $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAllowForTackleReview(): void
    {
        $client = $this->getBrowser();

        $url = sprintf('/tackles/review/%d/', $this->anonymizedTackleReview->getId());

        $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }
}

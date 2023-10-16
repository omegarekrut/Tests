<?php

namespace Tests\Functional\Domain\Record\Tackle\Command;

use App\Domain\Record\Tackle\Command\TackleReview\DeleteTackleReviewCommand;
use App\Domain\Record\Tackle\Entity\TackleReview;
use Tests\DataFixtures\ORM\Record\LoadTackleReviews;
use Tests\Functional\TestCase;

/**
 * @group tackleReview
 */
class DeleteTackleReviewHandlerTest extends TestCase
{
    public function testTackleReviewCanBeDeleted(): void
    {
        $referenceRepository = $this->loadFixtures([LoadTackleReviews::class])->getReferenceRepository();

        /** @var TackleReview $tackleReview */
        $tackleReview = $referenceRepository->getReference(LoadTackleReviews::getRandReferenceName());
        $tackleReviewId = $tackleReview->getId();

        $deleteTackleReviewCommand = new DeleteTackleReviewCommand($tackleReview);
        $this->getCommandBus()->handle($deleteTackleReviewCommand);

        $this->getEntityManager()->clear();

        $tackleReviewRepository = $this->getEntityManager()->getRepository(TackleReview::class);
        $deletedTackleReview = $tackleReviewRepository->findById($tackleReviewId);

        $this->assertNull($deletedTackleReview);
    }
}

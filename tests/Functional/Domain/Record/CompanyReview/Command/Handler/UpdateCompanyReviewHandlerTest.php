<?php

namespace Tests\Functional\Domain\Record\CompanyReview\Command\Handler;

use App\Domain\Record\CompanyReview\Command\UpdateCompanyReviewCommand;
use App\Domain\Record\CompanyReview\Entity\CompanyReview;
use App\Domain\Record\CompanyReview\Repository\CompanyReviewRepository;
use App\Util\ImageStorage\Collection\ImageCollection;
use Tests\DataFixtures\ORM\Record\CompanyReview\LoadCompanyReviews;
use Tests\Functional\TestCase;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class UpdateCompanyReviewHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadCompanyReviews::class,
        ])->getReferenceRepository();

        $companyReview = $referenceRepository->getReference(LoadCompanyReviews::REFERENCE_NAME);
        assert($companyReview instanceof CompanyReview);

        $command = new UpdateCompanyReviewCommand($companyReview);
        $command->text = $this->getFaker()->realText(100);
        $command->images = new ImageCollection([]);

        $this->getCommandBus()->handle($command);

        $companyReviewRepository = $this->getContainer()->get(CompanyReviewRepository::class);
        assert($companyReviewRepository instanceof CompanyReviewRepository);

        $companyReview = $companyReviewRepository->findById($command->companyReviewId);

        $this->assertEquals($command->text, $companyReview->getText());
        $this->assertEquals($command->images, $companyReview->getImages());
    }
}

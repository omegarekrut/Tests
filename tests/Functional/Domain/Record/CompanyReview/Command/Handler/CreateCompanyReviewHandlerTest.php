<?php

namespace Tests\Functional\Domain\Record\CompanyReview\Command\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyReview\Command\CreateCompanyReviewCommand;
use App\Domain\Record\CompanyReview\Repository\CompanyReviewRepository;
use App\Module\Author\AuthorInterface;
use App\Util\ImageStorage\Collection\ImageWithRotationAngleCollection;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageWithRotationAngle;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

class CreateCompanyReviewHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $author = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($author instanceof AuthorInterface);

        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        $companyReviewRepository = $this->getContainer()->get(CompanyReviewRepository::class);
        assert($companyReviewRepository instanceof CompanyReviewRepository);

        $exceptedTitle = $this->createTitle($author->getUsername(), $company->getName());
        $command = new CreateCompanyReviewCommand($author, $company);
        $command->images = $this->createImageRotationCollectionWithOneImage();
        $command->text = $this->getFaker()->realText(200);

        $this->getCommandBus()->handle($command);

        $companyReview = $companyReviewRepository->findByTitle($exceptedTitle);

        $this->assertEquals($exceptedTitle, $companyReview->getTitle());
        $this->assertEquals($command->text, $companyReview->getText());
        $this->assertEquals($command->author, $companyReview->getAuthor());
        $this->assertEquals($command->company, $companyReview->getCompany());
        $this->assertCount(count($command->images), $companyReview->getImages());
    }

    private function createTitle(string $authorName, string $companyName): string
    {
        return sprintf('Отзыв пользователя %s на компанию %s', $authorName, $companyName);
    }

    private function createImageRotationCollectionWithOneImage(): ImageWithRotationAngleCollection
    {
        $images = new Image('image name');
        $imageRotation = new ImageWithRotationAngle($images, 190);

        return new ImageWithRotationAngleCollection([$imageRotation]);
    }
}

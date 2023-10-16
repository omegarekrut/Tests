<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Company\Entity\Company;
use App\Domain\EventSubscriber\CompanyReviewEventsSubscriber;
use App\Domain\Record\CompanyReview\Command\CreateCompanyReviewCommand;
use App\Domain\Record\CompanyReview\Entity\CompanyReview;
use App\Domain\Record\CompanyReview\Event\CompanyReviewCreatedEvent;
use App\Domain\Record\CompanyReview\Repository\CompanyReviewRepository;
use App\Domain\User\Command\Notification\NotifyEmployeesCompanyReviewCreatedCommand;
use App\Module\Author\AuthorInterface;
use App\Util\ImageStorage\Collection\ImageWithRotationAngleCollection;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\CommandBusMock;

/**
 * @group company-review-events
 */
class CompanyReviewEventsSubscriberTest extends TestCase
{
    public function testNotificationMustBeSentAfterCreationReviewForCompany(): void
    {
        $commandBusMock = new CommandBusMock();
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $eventDispatcher->addSubscriber(new CompanyReviewEventsSubscriber($commandBusMock));

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        $authorReview = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($authorReview instanceof AuthorInterface);

        $companyReview = $this->createReviewForCompany($authorReview, $company);

        $eventDispatcher->dispatch(new CompanyReviewCreatedEvent($companyReview));

        $this->assertTrue($commandBusMock->isHandled(NotifyEmployeesCompanyReviewCreatedCommand::class));
    }

    private function createReviewForCompany(AuthorInterface $authorReview, Company $company): CompanyReview
    {
        $createCompanyReviewCommand = new CreateCompanyReviewCommand($authorReview, $company);
        $createCompanyReviewCommand->text = $this->getFaker()->realText(200);
        $createCompanyReviewCommand->images = new ImageWithRotationAngleCollection([]);
        $reviewTitle = $this->createTitle(
            $createCompanyReviewCommand->author->getUsername(),
            $createCompanyReviewCommand->company->getName()
        );

        $this->getCommandBus()->handle($createCompanyReviewCommand);

        $companyReviewRepository = $this->getContainer()->get(CompanyReviewRepository::class);
        $companyReview = $companyReviewRepository->findByTitle($reviewTitle);
        assert($companyReview instanceof CompanyReview);

        return $companyReview;
    }

    private function createTitle(string $authorName, string $companyName): string
    {
        return sprintf('Отзыв пользователя %s на компанию %s', $authorName, $companyName);
    }
}

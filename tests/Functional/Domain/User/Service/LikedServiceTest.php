<?php

namespace Tests\Functional\Domain\User\Service;

use App\Bridge\Xenforo\ForumApiInterface;
use App\Bridge\Xenforo\Provider\LikedUsersProviderInterface;
use App\Bridge\Xenforo\Provider\Mock\LikedUsersProvider;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Command\Rating\RecalculateUsersRatingForPreviousDayEventsCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Service\LikedService;
use App\Module\Voting\VoteStorage;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\Record\LoadTackles;
use Tests\DataFixtures\ORM\User\LoadUserWithHighRating;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;
use Tests\Functional\TestCase;

class LikedServiceTest extends TestCase
{
    /** @var Record */
    private $record;

    /** @var User */
    private $user;

    /** @var User */
    private $votingUser;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithoutRecords::class,
            LoadUserWithHighRating::class,
            LoadTackles::class,
        ])->getReferenceRepository();

        $this->record = $referenceRepository->getReference(LoadTackles::getRandReferenceName());
        $this->user = $referenceRepository->getReference(LoadUserWithoutRecords::REFERENCE_NAME);
        $this->votingUser = $referenceRepository->getReference(LoadUserWithHighRating::REFERENCE_NAME);

        $this->record->updateAuthor($this->user);
    }

    protected function tearDown(): void
    {
        unset(
            $this->record,
            $this->user,
            $this->votingUser
        );

        parent::tearDown();
    }

    public function testGetCollectionWithUserGettingLikeForContentOnSite(): void
    {
        /** @var VoteStorage $voteStorage */
        $voteStorage = $this->getContainer()->get(VoteStorage::class);

        $voteMoment = Carbon::yesterday();
        $commandMoment = Carbon::now();

        Carbon::setTestNow($voteMoment);

        $voteStorage->addVote(1, $this->votingUser, $this->record, '127.0.0.1');

        Carbon::setTestNow($commandMoment);

        try {
            $likedService = new LikedService(
                $this->getContainer()->get(ForumApiInterface::class),
                $voteStorage,
                $this->getContainer()->get(UserRepository::class)
            );

            $userCollection = $likedService->getUserAreRatedForDay($voteMoment);

            $this->assertTrue($userCollection->contains($this->record->getAuthor()));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function testNotGetCollectionWithUserGettingLikeForContentOnSiteInAnotherDay(): void
    {
        /** @var VoteStorage $voteStorage */
        $voteStorage = $this->getContainer()->get(VoteStorage::class);

        $voteMoment = Carbon::yesterday();
        $commandMoment = Carbon::tomorrow();

        Carbon::setTestNow($voteMoment);

        $voteStorage->addVote(1, $this->votingUser, $this->record, '127.0.0.1');

        Carbon::setTestNow($commandMoment);

        try {
            $likedService = new LikedService(
                $this->getContainer()->get(ForumApiInterface::class),
                $voteStorage,
                $this->getContainer()->get(UserRepository::class)
            );

            $userCollection = $likedService->getUserAreRatedForDay($commandMoment);

            $this->assertFalse($userCollection->contains($this->record->getAuthor()));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function testGetCollectionWithUserGetLikeOnForumInSomeday(): void
    {
        $commandMoment = Carbon::now();

        $forumApi = $this->getContainer()->get(ForumApiInterface::class);

        /** @var LikedUsersProvider $likedUsersProvider */
        $likedUsersProvider = $this->getContainer()->get(LikedUsersProviderInterface::class);
        $likedUsersProvider->setLikedForumUserIdsForDay($commandMoment, [$this->user->getForumUserId()]);

        $forumApi->addProvider($likedUsersProvider);

        Carbon::setTestNow($commandMoment);

        try {
            $likedService = new LikedService(
                $forumApi,
                $this->getContainer()->get(VoteStorage::class),
                $this->getContainer()->get(UserRepository::class)
            );

            $userCollection = $likedService->getUserAreRatedForDay($commandMoment);

            $this->assertTrue($userCollection->contains($this->user));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function testGetCollectionWithUsersGettingLikeOnForumAndSite(): void
    {
        /** @var VoteStorage $voteStorage */
        $voteStorage = $this->getContainer()->get(VoteStorage::class);
        /** @var ForumApiInterface $forumApi */
        $forumApi = $this->getContainer()->get(ForumApiInterface::class);

        $voteMoment = Carbon::yesterday();
        $commandMoment = Carbon::now();

        Carbon::setTestNow($voteMoment);

        $voteStorage->addVote(1, $this->votingUser, $this->record, '127.0.0.1');

        /** @var LikedUsersProvider $likedUsersProvider */
        $likedUsersProvider = $this->getContainer()->get(LikedUsersProviderInterface::class);
        $likedUsersProvider->setLikedForumUserIdsForDay($voteMoment, [$this->user->getForumUserId(), $this->votingUser->getForumUserId()]);

        $forumApi->addProvider($likedUsersProvider);

        Carbon::setTestNow($commandMoment);

        try {
            $likedService = new LikedService(
                $forumApi,
                $this->getContainer()->get(VoteStorage::class),
                $this->getContainer()->get(UserRepository::class)
            );

            $userCollection = $likedService->getUserAreRatedForDay($voteMoment);

            $this->assertTrue($userCollection->contains($this->record->getAuthor()));
            $this->assertTrue($userCollection->contains($this->votingUser));
            $this->assertTrue($this->votingUser !== $this->record->getAuthor());
        } finally {
            Carbon::setTestNow();
        }
    }
}

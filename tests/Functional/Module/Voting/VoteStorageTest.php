<?php

namespace Tests\Functional\Module\Voting;

use App\Domain\User\Entity\User;
use App\Module\Voting\Entity\AnonymousVoter;
use App\Module\Voting\Entity\Vote;
use App\Module\Voting\VoteStorage;
use Tests\DataFixtures\ORM\User\LoadUserWhoVotedForRecord;
use Tests\Functional\TestCase;

class VoteStorageTest extends TestCase
{
    public function testAnonymizeVoterVotes(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWhoVotedForRecord::class,
        ])->getReferenceRepository();

        /** @var VoteStorage $voteStorage */
        $voteStorage = $this->getContainer()->get(VoteStorage::class);

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadUserWhoVotedForRecord::REFERENCE_NAME);

        $votes = $voteStorage->getVoterVotes($user);

        $voteStorage->anonymizeVoterVotes($user);

        /** @var Vote $vote */
        foreach ($votes as $vote) {
            $this->assertInstanceOf(AnonymousVoter::class, $vote->getVoter());
        }
    }
}

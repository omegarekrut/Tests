<?php

namespace Tests\Unit\Module\Voting\Mock;

use App\Module\Voting\Entity\VotableIdentifier;
use App\Module\Voting\VotableInterface;

class VotableMock implements VotableInterface
{
    private $votableId;

    public function __construct(VotableIdentifier $votableId)
    {
        $this->votableId = $votableId;
    }

    public function getVotableId(): VotableIdentifier
    {
        return $this->votableId;
    }
}

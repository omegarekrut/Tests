<?php

namespace Tests\Unit\Util\Security\Assertion;

use App\Util\Security\AssertionSubject\GroupMemberInterface;
use Ramsey\Uuid\UuidInterface;

class GroupMemberSubject implements GroupMemberInterface
{
    private $id;
    private $methodCallsLog;

    public function setId(UuidInterface $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): UuidInterface
    {
        $this->methodCallsLog[] = 'getId';

        return $this->id;
    }

    public function wasCalled(string $method): bool
    {
        return in_array($method, $this->methodCallsLog);
    }
}

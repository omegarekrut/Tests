<?php

namespace Tests\Unit\Util\Security\Assertion;

use App\Util\Security\AssertionSubject\CreatedInterface;
use App\Util\Security\AssertionSubject\HasOwnerInterface;
use App\Util\Security\AssertionSubject\OwnerInterface;

class Subject implements CreatedInterface, HasOwnerInterface, OwnerInterface
{
    private $id;
    private $created;
    private $methodCallsLog;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        $this->methodCallsLog[] = 'getId';

        return $this->id;
    }

    public function setCreatedAt(\DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        $this->methodCallsLog[] = 'getCreated';

        return $this->created;
    }

    public function getOwner(): OwnerInterface
    {
        $this->methodCallsLog[] = 'getOwner';

        return $this;
    }

    public function getUserName(): string
    {
        return 'Subject';
    }

    public function __toString(): string
    {
        return $this->id ?? 'name';
    }

    public function wasCalled(string $method): bool
    {
        return in_array($method, $this->methodCallsLog);
    }
}

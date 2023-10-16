<?php

namespace Tests\Unit\Util\Acl;

use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

class Assertion implements AssertionInterface
{
    private $assertResult;

    public function __construct(bool $assertResult)
    {
        $this->assertResult = $assertResult;
    }

    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        return $this->assertResult;
    }
}

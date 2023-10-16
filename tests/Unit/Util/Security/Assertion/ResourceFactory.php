<?php

namespace Tests\Unit\Util\Security\Assertion;

use App\Util\Acl\SubjectResource;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

trait ResourceFactory
{
    /**
     * @param mixed $subject
     */
    protected function createSubjectResource($subject): SubjectResource
    {
        return new SubjectResource('foo/bar', $subject);
    }

    protected function createNoSubjectResource(): ResourceInterface
    {
        return $this->createMock(ResourceInterface::class);
    }
}

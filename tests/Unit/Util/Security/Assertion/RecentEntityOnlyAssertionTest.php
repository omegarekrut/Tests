<?php

namespace Tests\Unit\Util\Security\Assertion;

use App\Domain\Draft\Entity\Draft;
use App\Util\Security\Assertion\RecentEntity;
use Carbon\Carbon;
use InvalidArgumentException;
use Tests\Unit\TestCase;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

class RecentEntityOnlyAssertionTest extends TestCase
{
    use ResourceFactory;

    private RecentEntity $recentEntityAssertion;

    protected function setUp(): void
    {
        parent::setUp();

        $weekAgo = Carbon::now()->subWeeks(1);
        $this->recentEntityAssertion = new RecentEntity([Subject::class], $weekAgo);
    }

    protected function tearDown(): void
    {
        unset($this->recentEntityAssertion);

        parent::tearDown();
    }

    public function testAccessGranted(): void
    {
        $subject = (new Subject())->setCreatedAt(Carbon::now());
        $resource = $this->createSubjectResource($subject);

        $allow = $this->recentEntityAssertion->assert(new Acl(), null, $resource);
        $this->assertTrue($subject->wasCalled('getCreated'));
        $this->assertTrue($allow);
    }

    public function testAccessDeny(): void
    {
        $twoWeekAgo = Carbon::now()->subWeeks(2);
        $oldSubject = (new Subject())->setCreatedAt($twoWeekAgo);
        $resource = $this->createSubjectResource($oldSubject);

        $allow = $this->recentEntityAssertion->assert(new Acl(), null, $resource);
        $this->assertTrue($oldSubject->wasCalled('getCreated'));
        $this->assertFalse($allow);
    }

    public function testUnsupportedResourceWithoutSubject(): void
    {
        $resourceWithoutSubject = $this->createNoSubjectResource();

        $isAllow = $this->recentEntityAssertion->assert(new Acl(), null, $resourceWithoutSubject);

        $this->assertTrue($isAllow);
    }

    public function testUnsupportedResourceWithInvalidInterface(): void
    {
        $resourceWithInvalidInterface = $this->createSubjectResource(null);

        $isAllow = $this->recentEntityAssertion->assert(new Acl(), null, $resourceWithInvalidInterface);

        $this->assertTrue($isAllow);
    }

    public function testUnsupportedResourceWithoutHasOwnerInterfaceImplementation(): void
    {
        $resourceWithoutHasOwnerInterfaceImplementation = $this->createSubjectResource($this->createMock(Draft::class));

        $isAllow = $this->recentEntityAssertion->assert(new Acl(), null, $resourceWithoutHasOwnerInterfaceImplementation);

        $this->assertTrue($isAllow);
    }

    public function testUnsupportedResourceWithAnotherTypeOfEntity(): void
    {
        $resourceWithoutHasOwnerInterfaceImplementation = $this->createSubjectResource($this->createAnotherTypeSubject());

        $isAllow = $this->recentEntityAssertion->assert(new Acl(), null, $resourceWithoutHasOwnerInterfaceImplementation);

        $this->assertTrue($isAllow);
    }

    public function testAssertionCanBeCreatedWithNotExistsClassArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$subjectClass must be exists class');

        new RecentEntity(['not exists class name'], Carbon::now());
    }

    private function createAnotherTypeSubject(): ResourceInterface
    {
        return $this->createMock(ResourceInterface::class);
    }
}

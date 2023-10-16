<?php

namespace Tests\Unit\Util\Security\Assertion;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Company\Entity\Company;
use App\Util\Security\Assertion\PublicOnly;
use Laminas\Permissions\Acl\Acl;
use PHPUnit\Framework\TestCase;

class PublicOnlyTest extends TestCase
{

    use ResourceFactory;

    private PublicOnly $publicOnlyAssertion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publicOnlyAssertion = new PublicOnly();
    }

    public function testFalseAssertOnNonPublic(): void
    {
        $nonPublicCompany = $this->createMock(Company::class);
        $nonPublicCompany->method('isPublic')->willReturn(false);
        $nonPublicCompanySubject = $this->createSubjectResource($nonPublicCompany);

        $allow = $this->publicOnlyAssertion->assert(new Acl(), null, $nonPublicCompanySubject, null);

        $this->assertFalse($allow);
    }

    public function testTrueAssertOnNonPublic(): void
    {
        $publicCompany = $this->createMock(Company::class);
        $publicCompany->method('isPublic')->willReturn(true);
        $pubicCompanySubject = $this->createSubjectResource($publicCompany);

        $allow = $this->publicOnlyAssertion->assert(new Acl(), null, $pubicCompanySubject, null);

        $this->assertTrue($allow);
    }

    public function testThrowExceptionOnNonSupportedResource(): void
    {
        $nonSupportedResource = $this->createMock(Comment::class);
        $nonSupportedSubject = $this->createSubjectResource($nonSupportedResource);

        $this->expectExceptionMessage('Утверждение PublicOnly не поддерживает для ресурса');
        $this->publicOnlyAssertion->assert(new Acl(), null, $nonSupportedSubject, null);
    }
}

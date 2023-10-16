<?php

namespace Tests\Unit\Twig\Author;

use App\Domain\User\Entity\User;
use App\Module\Author\AuthorInterface;
use App\Twig\Author\AuthorPublicViewLinkRenderer;
use App\Twig\User\AuthorProfileUrlGenerator;
use App\Twig\User\UserProfileUrlGenerator;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class AuthorPublicViewLinkRendererTest extends TestCase
{
    /** @var UserProfileUrlGenerator */
    private $userProfileUrlGenerator;
    /** @var AuthorPublicViewLinkRenderer */
    private $authorPublicViewLinkRenderer;

    protected function setUp(): void
    {
        $this->userProfileUrlGenerator = $this->createConfiguredMock(UserProfileUrlGenerator::class, [
            '__invoke' => '/some/link',
        ]);
        $this->authorPublicViewLinkRenderer = new AuthorPublicViewLinkRenderer($this->userProfileUrlGenerator);
    }

    public function testLinkForSimpleAuthorShouldBeEqualsAuthorUsername(): void
    {
        $author = $this->createMock(AuthorInterface::class);

        $link = ($this->authorPublicViewLinkRenderer)($author);

        $this->assertEquals($author->getUsername(), $link);
    }

    public function testLinkForUserAuthorMustBeEqualsUserLink(): void
    {
        $author = $this->createConfiguredMock(User::class, [
            'getUsername' => 'test-user',
        ]);

        $this->assertEquals(
            '<a href="/some/link"  target="_blank">test-user</a>',
            ($this->authorPublicViewLinkRenderer)($author)
        );
    }

    public function testLinkShouldContainsAdditionsAttributes(): void
    {
        $author = $this->createConfiguredMock(User::class, [
            'getUsername' => 'test-user',
        ]);
        $link = ($this->authorPublicViewLinkRenderer)($author, 'expected-class', true);

        $this->assertStringContainsString('class="expected-class"',$link);
        $this->assertStringContainsString('target="_blank"',$link);
    }
}

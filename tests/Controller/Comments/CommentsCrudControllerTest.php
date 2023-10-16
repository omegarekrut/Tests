<?php

namespace Tests\Controller\Comments;

use App\Domain\Comment\Entity\Comment;
use App\Domain\User\Entity\User;
use Generator;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Comment\LoadAnswersToComments;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;

class CommentsCrudControllerTest extends TestCase
{
    public function testRestoreThread(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAnswersToComments::class,
        ])->getReferenceRepository();

        $comment = $referenceRepository->getReference(LoadAnswersToComments::getRandReferenceName());
        assert($comment instanceof Comment);

        $recordAuthor = $comment->getRecord()->getAuthor();
        $browser = $this->getBrowser()
            ->loginUser($recordAuthor);

        $browser->request('GET', sprintf('/comment/%s/hide/cascade/', $comment->getParentComment()->getSlug()));

        $this->assertTrue($browser->getResponse()->isRedirect(sprintf(
            '/articles/view/%d/#comment%s',
            $comment->getParentComment()->getRecord()->getId(),
            $comment->getParentComment()->getSlug()
        )));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Комментарий скрыт со всеми ответами', $viewPage->html());

        $this->assertStringContainsString(
            'Комментарий скрыт автором материала.',
            $viewPage->filter(sprintf(
                '.js-comment-text%s .js-comment-text',
                $comment->getSlug()
            ))->first()->text()
        );

        $browser->request('GET', sprintf('/comment/%s/restore/cascade/', $comment->getParentComment()->getSlug()));

        $this->assertTrue($browser->getResponse()->isRedirect(sprintf(
            '/articles/view/%d/#comment%s',
            $comment->getParentComment()->getRecord()->getId(),
            $comment->getParentComment()->getSlug()
        )));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Комментарий восстановлен со всеми ответами', $viewPage->html());

        $this->assertStringNotContainsString(
            'Комментарий скрыт автором материала.',
            $viewPage->html()
        );
    }

    /**
     * @dataProvider allowAccessDataProvider
     */
    public function testAllowAccess(string $fixture, string $reference): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAnswersToComments::class,
            $fixture,
        ])->getReferenceRepository();

        $comment = $referenceRepository->getReference(LoadAnswersToComments::getRandReferenceName());
        assert($comment instanceof Comment);

        $admin = $referenceRepository->getReference($reference);
        assert($admin instanceof User);

        $browser = $this->getBrowser()
            ->loginUser($admin);

        $browser->request('GET', sprintf('/comment/%s/hide/cascade/', $comment->getParentComment()->getSlug()));

        $this->assertTrue($browser->getResponse()->isRedirect(sprintf(
            '/articles/view/%d/#comment%s',
            $comment->getParentComment()->getRecord()->getId(),
            $comment->getParentComment()->getSlug()
        )));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Комментарий скрыт со всеми ответами', $viewPage->html());

        $browser->request('GET', sprintf('/comment/%s/restore/cascade/', $comment->getParentComment()->getSlug()));

        $this->assertTrue($browser->getResponse()->isRedirect(sprintf(
            '/articles/view/%d/#comment%s',
            $comment->getParentComment()->getRecord()->getId(),
            $comment->getParentComment()->getSlug()
        )));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Комментарий восстановлен со всеми ответами', $viewPage->html());
    }

    public function allowAccessDataProvider(): Generator
    {
        yield [
            LoadAdminUser::class,
            LoadAdminUser::REFERENCE_NAME,
        ];

        yield [
            LoadModeratorUser::class,
            LoadModeratorUser::REFERENCE_NAME,
        ];
    }
}

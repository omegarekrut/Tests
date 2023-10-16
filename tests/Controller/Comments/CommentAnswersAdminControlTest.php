<?php

namespace Tests\Controller\Comments;

use App\Domain\Comment\Entity\Comment;
use App\Domain\User\Entity\User;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Comment\LoadAnswersToComments;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

class CommentAnswersAdminControlTest extends TestCase
{
    public function testRestoreThread(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAnswersToComments::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $comment = $referenceRepository->getReference(sprintf('%s-%d', LoadAnswersToComments::REFERENCE_NAME, 1));
        assert($comment instanceof Comment);

        $article = $comment->getRecord();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);
        $url = sprintf("/articles/view/%s/", $article->getId());

        $page = $client->request('GET', $url);

        $link = $page->selectLink('Скрыть ветку')->link();
        $client->click($link);
        $viewPage = $client->followRedirect();
        $this->assertSeeAlertInPageContent('success', 'Комментарий скрыт со всеми ответами', $viewPage->html());


        $linkReload = $viewPage->selectLink('Восстановить ветку')->link();
        $client->click($linkReload);
        $viewPageReload = $client->followRedirect();
        $this->assertSeeAlertInPageContent('success', 'Комментарий восстановлен со всеми ответами', $viewPageReload->html());
    }
}

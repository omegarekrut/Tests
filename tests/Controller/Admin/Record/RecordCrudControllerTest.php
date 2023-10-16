<?php

namespace Tests\Controller\Admin\Record;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\User\Entity\User;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleWithManyComments;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;

class RecordCrudControllerTest extends TestCase
{
    public function testUpdateAuthor(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadMostActiveUser::class,
            LoadArticleWithManyComments::class,
        ])->getReferenceRepository();

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);

        $user = $referenceRepository->getReference(LoadMostActiveUser::USER_MOST_ACTIVE);
        assert($user instanceof User);

        $article = $referenceRepository->getReference(LoadArticleWithManyComments::REFERENCE_NAME);
        assert($article instanceof Article);

        $browser = $this->getBrowser()->loginUser($admin);

        $url = sprintf('/admin/record/%s/author/', $article->getId());

        $browser->request('GET', $url);

        $browser->submitForm('Сохранить', [
            'record_author[author]' => $user->getUserName(),
        ]);

        $this->assertTrue($browser->getResponse()->isRedirect('/admin/record/'));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Автор записи изменен успешно.', $viewPage->html());
    }
}

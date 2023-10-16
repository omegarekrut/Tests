<?php

namespace Tests\Acceptance\Comments;

use Tester;

class AnswerToCommentPageCest
{
    /* todo После перехода с codecept на phpunit реализовать тест по проверке наличия ответа на коммент на странице */

    public function dontSeeAnswersToComment(Tester $I): void
    {
        $articleId = $I->grabActiveRecordIdByTypeWhereNotHasAnswersToComment('article');

        if (!empty($articleId)) {
            $I->amOnPage(sprintf('/articles/view/%d/', $articleId));
            $I->dontseeElement('.comment-answers__block');
            $I->dontSeeElement('.commentsFS__parent-comment');
        }
    }
}

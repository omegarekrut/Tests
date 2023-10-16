<?php

namespace Tests\Acceptance\Comments;

use Codeception\Example;
use Codeception\Util\HttpCode;
use Tester;
use Tests\Acceptance\Traits\CommentTrait;
use Tests\Support\TransferObject\User;

class CommentRateCest
{
    use CommentTrait;

    /** @var User */
    protected $user;
    protected $anotherUser;

    protected $tidingPage;
    protected $rateUrl;

    public function _before(Tester $I): void
    {
        $this->user = $this->user ?: $I->findNotBannedUser();

        if (empty($this->tidingPage)) {
            $this->createComment($I);
        } else {
            $I->amOnPage($this->tidingPage);
        }
    }

    private function createComment(Tester $I): void
    {
        $I->authAs($this->user);

        $I->amOnPage('/articles/');
        $I->click('a.articleFS__content__link');

        $this->tidingPage = $I->getCurrentUrl();

        $this->fillCommentCreationFormAndSubmit($I);
    }

    protected function ratingSelectors(): array
    {
        return [
            ['selector' => '.js-comments .commentsFS__container:last-child a.rating__block__reduce'],
            ['selector' => '.js-comments .commentsFS__container:last-child a.rating__block__increase'],
        ];
    }

    /**
     * @dataProvider ratingSelectors
     */
    public function deniedRateOwnComment(Tester $I, Example $example): void
    {
        $I->authAs($this->user);

        $I->amOnPage($this->tidingPage);

        $this->rateUrl = $I->grabAttributeFrom($example['selector'], 'href');

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxGetRequest($this->rateUrl);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    /**
     * @depends deniedRateOwnComment
     */
    public function cantRateCommentAsNotAuthorizedUser(Tester $I): void
    {
        $I->amOnPage($this->rateUrl);

        $I->seeInTitle('Войти на сайт');
    }

    /**
     * @dataProvider ratingSelectors
     */
    public function rateComment(Tester $I, Example $example): void
    {
        $this->createComment($I);
        $this->anotherUser = $I->findAnotherUserInGroup($this->user);

        $I->authAs($this->anotherUser);
        $I->amOnPage($this->tidingPage);

        $this->rateUrl = $I->grabAttributeFrom($example['selector'], 'href');

        $I->amOnPage($this->rateUrl);
        $I->seeResponseCodeIs(HttpCode::OK);

        $rateValue = (int) $I->grabTextFrom($example['selector'].' .rating__block__count');

        $I->assertEquals(1, $rateValue);
    }

    /**
     * @depends rateComment
     */
    public function rateCommentTwice(Tester $I): void
    {
        $I->authAs($this->anotherUser);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxGetRequest($this->rateUrl);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }
}

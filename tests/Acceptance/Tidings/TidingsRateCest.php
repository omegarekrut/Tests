<?php

namespace Tests\Acceptance\Tidings;

use Codeception\Example;
use Codeception\Util\HttpCode;
use Faker\Generator;
use Tester;
use Tests\Support\TransferObject\User;

class TidingsRateCest
{
    /** @var User */
    private $tidingsAuthor;
    /** @var User */
    private $anotherUser;
    /** @var Generator */
    private $faker;

    private $tidingPage;
    private $rateUrl;

    public function _before(Tester $I): void
    {
        $this->faker = $I->getFaker();
        $this->tidingsAuthor = $this->tidingsAuthor ?? $I->findNotBannedUser();
        $this->anotherUser = $this->anotherUser ?? $I->findAnotherUserWhoCanVoteToRecord($this->tidingsAuthor);
        $this->tidingPage = $this->tidingPage ?? $this->tidingPage = $this->createTidings($I);

        $I->amOnPage($this->tidingPage);
    }

    private function createTidings(Tester $I): string
    {
        $I->authAs($this->tidingsAuthor);

        $I->amOnPage('/tidings/');
        $I->click('.add-record');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->fillField('tidings[title]', $this->faker->realText(50));
        $I->fillField('tidings[text]', $this->faker->realText(50));
        $I->fillField('tidings[regionId]', $I->getRandomRegionId());

        $I->click('Опубликовать');

        $I->seeAlert('success', 'Ваша запись успешно добавлена.');

        return $I->getCurrentUrl();
    }

    protected function ratingSelectors(): array
    {
        return [
            ['selector' => 'section.articleFS section:last-child a.rating__block__reduce'],
            ['selector' => 'section.articleFS section:last-child a.rating__block__increase'],
        ];
    }

    /**
     * @dataProvider ratingSelectors
     */
    public function deniedRateOwnTidings(Tester $I, Example $example): void
    {
        $I->authAs($this->tidingsAuthor);

        $I->amOnPage($this->tidingPage);

        $this->rateUrl = $I->grabAttributeFrom($example['selector'], 'href');

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxGetRequest($this->rateUrl);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    /**
     * @depends deniedRateOwnTidings
     */
    public function cantRateTidingsAsNotAuthorizedUser(Tester $I): void
    {
        $I->amOnPage($this->rateUrl);

        $I->seeInTitle('Войти на сайт');
    }

    /**
     * @depends deniedRateOwnTidings
     */
    public function rateTidings(Tester $I): void
    {
        $I->authAs($this->anotherUser);
        $I->amOnPage($this->tidingPage);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxGetRequest($this->rateUrl);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->deleteHeader('X-Requested-With');
        $I->amOnPage($this->tidingPage);

        $rateValue = (int) $I->grabTextFrom('section.articleFS section:last-child .articleFS__footer__right .rating__block .rating__block__value');

        $I->assertEquals(1, $rateValue);
    }

    /**
     * @depends rateTidings
     */
    public function cantRateTidingsTwice(Tester $I): void
    {
        $I->authAs($this->anotherUser);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxGetRequest($this->rateUrl);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }
}

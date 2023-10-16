<?php

namespace Tests\Acceptance\Admin\Ban;

use Codeception\Util\HttpCode;
use Faker;
use Tester;
use Tests\DataFixtures\ORM\User\LoadUserForBan;

/**
 * @group ban
 */
class UserBanCest
{
    /**
     * @var string|null
     */
    private $banedUserName;

    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
    }

    public function seeUsersBans(Tester $I): void
    {
        $I->amOnPage('/admin/ban/user/');
        $I->see('Бан по пользователям');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function createBanForUser(Tester $I): void
    {
        $this->banedUserName = $this->grabUsernameForBan($I);

        /**
         * @var $faker Faker\Generator
         */
        $faker = $I->getFaker();

        $I->amOnPage('/admin/ban/user/');
        $I->click('Добавить бан');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Добавить бан пользователю');

        $I->fillField('ban_user[user]', $this->banedUserName);
        $I->fillField('ban_user[cause]', $faker->text());
        $I->fillField('ban_user[expiredAt]', $faker->date('Y-m-d', 'yesterday'));

        $I->click('Сохранить');

        $I->seeAlert('success', 'Пользователь успешно забанен.');
    }

    /**
     * @depends createBanForUser
     */
    public function searchBanByUsername(Tester $I): void
    {
        $I->amOnPage(sprintf('/admin/ban/user/?ban_user_search[user]=%s', $this->banedUserName));
        $I->see('Бан по пользователям');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeInSource(sprintf('>%s<', $this->banedUserName));
    }

    /**
     * @depends createBanForUser
     */
    public function editUserBan(Tester $I): void
    {
        $I->amOnPage('/admin/ban/user/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->click('//a[@title="Изменить"]');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Редактировать бан пользователя');

        $I->fillField('ban_user[cause]', $I->getFaker()->text());
        $I->click('Сохранить');

        $I->seeAlert('success', 'Бан пользователя успешно обновлен.');
    }

    /**
     * @depends createBanForUser
     */
    public function seeAllBansForUser(Tester $I): void
    {
        $I->amOnPage('/admin/ban/user/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->click('//a[@title="Просмотреть"]');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Баны пользователя');
    }

    private function grabUsernameForBan(Tester $I): string
    {
        return (string) $I->grabFromDatabase('users', 'login', [
            'login' => LoadUserForBan::REFERENCE_NAME,
            'group' => 'user',
        ]);
    }
}

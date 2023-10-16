<?php

namespace Tests\Acceptance\Admin\Ban;

use Codeception\Util\HttpCode;
use Faker;
use Tester;

/**
 * @group ban
 */
class IpBanCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
    }

    public function seeIpsBan(Tester $I): void
    {
        $I->amOnPage('/admin/ban/ip/');
        $I->see('Бан по IP');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function createBanForIp(Tester $I): void
    {
        /**
         * @var $faker Faker\Generator
         */
        $faker = $I->getFaker();

        $I->amOnPage('/admin/ban/ip/');
        $I->click('Добавить бан');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Добавить бан по ip');

        $I->fillField('ban_ip[ipRange]', $faker->ipv4());
        $I->fillField('ban_ip[cause]', $faker->realText(50));
        $I->fillField('ban_ip[expiredAt]', $faker->date());

        $I->click('Сохранить');

        $I->seeAlert('success', 'Бан по ip успешно добавлен.');
    }

    /**
     * @depends createBanForIp
     */
    public function updateBanForIp(Tester $I): void
    {
        $I->amOnPage('/admin/ban/ip/');
        $I->click('//a[@title="Изменить"]');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Редактировать бан по ip');

        $I->fillField('ban_ip[cause]', $I->getFaker()->text());

        $I->click('Сохранить');
        $I->seeAlert('success','Бан по ip успешно обновлен.');
    }
}

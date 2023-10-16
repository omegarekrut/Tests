<?php

namespace Tests\Acceptance\Profile;

use App\Domain\User\Entity\ValueObject\FishingInformation;
use Codeception\Util\HttpCode;
use Tester;

class ProfileUpdatingCest
{
    private $user;

    public function editMainInformation(Tester $I): void
    {
        $this->authAsUserWithRealEmailAccount($I);
        $I->see('Основное');

        $newLogin = substr(uniqid($I->getFaker()->username, false), 0, 15);
        $this->user->username = $newLogin;

        $I->fillField('#basic_information_login', $newLogin);
        $I->fillField('#basic_information_name', $I->getFaker()->username);
        $I->click('Сохранить');

        $I->seeAlert('success', 'Информация успешно обновлена.');
    }

    public function editFishInformation(Tester $I): void
    {
        $this->authAsUserWithLinkedAccount($I);
        $I->click('Рыбалка');

        $I->selectOption('#fishing_information_fishingTypes', FishingInformation::FISHING_TYPES[0]);
        $I->fillField('fishing_information[watercraft]', bin2hex(random_bytes(15)));
        $I->fillField('#fishing_information_aboutMe', bin2hex(random_bytes(15)));

        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Информация успешно обновлена.');
    }

    public function seeSocialSettingPage(Tester $I): void
    {
        $this->authAsUserWithLinkedAccount($I);
        $I->click('Соц. сети');

        $I->seeResponseCodeIs(HttpCode::OK);

        $I->see('vkontakte', '.related-social-networks__item');
        $I->see('odnoklassniki', '.no-related-social-networks__item');
        $I->see('mailru', '.no-related-social-networks__item');
        $I->see('yandex', '.no-related-social-networks__item');
        $I->see('google', '.no-related-social-networks__item');
    }

    private function authAsUserWithRealEmailAccount(Tester $I): void
    {
        $this->user = $I->findUserByCriteria(['email' => 'tigant@gmail.com']);
        $this->goToEditProfilePage($I);
    }

    private function authAsUserWithLinkedAccount(Tester $I): void
    {
        $this->user = $I->findUserWithLinkedAccount('vkontakte');
        $this->goToEditProfilePage($I);
    }

    private function goToEditProfilePage(Tester $I): void
    {
        $I->authAs($this->user);

        $I->click('.user__menu-avatar');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click('Редактировать профиль');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}

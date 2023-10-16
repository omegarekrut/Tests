<?php

namespace Tests\Acceptance\Admin\Notification;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group notification
 */
class CustomNotificationCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/custom-notification/');
    }

    public function seeNotificationList(Tester $I): void
    {
        $I->see('Список уведомлений');
    }

    public function createNotification(Tester $I): void
    {
        $I->click('Добавить уведомление');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Добавление уведомления');

        $I->fillField('custom_notification[title]', 'some title');
        $I->fillField('custom_notification[message]', 'New message');
        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Уведомление успешно сохранено и скоро будет разослано всем пользователям.');

        $I->seeInDatabase('custom_notifications', [
            'title' => 'some title',
            'message' => 'New message',
        ]);
    }

    public function editNotification(Tester $I): void
    {
        $I->click('//a[@title="Изменить"]');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Редактирование уведомления');

        $title = $I->getFaker()->realText(50, 1);
        $message = $I->getFaker()->realText(150, 1);

        $I->fillField('custom_notification[title]',  $title);
        $I->fillField('custom_notification[message]', $message);
        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Уведомление успешно обновлено.');

        $I->seeInDatabase('custom_notifications', [
            'title' => $title,
            'message' => $message,
        ]);
    }
}

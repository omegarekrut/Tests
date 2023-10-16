<?php

namespace Tests\Acceptance;

use App\Domain\User\Generator\SubscribeNewsletterHashGenerator;
use Codeception\Example;
use Codeception\Util\HttpCode;
use Tester;
use Tests\Support\TransferObject\User;

/**
 * @group subscription
 */
class SubscribePageCest
{
    private $hash;
    /** @var User */
    private $user;

    private const USER_SUBSCRIBE_URI_TEMPLATE = '/subscribe-to-user/%d/';
    private const USER_UNSUBSCRIBE_URI_TEMPLATE = '/unsubscribe-from-user/%d/';

    public function _before(Tester $I): void
    {
        $this->user = $I->findNotBannedUser();

        $hashGenerator = new SubscribeNewsletterHashGenerator(getenv('SUBSCRIBE_SALT'));
        $this->hash = $hashGenerator->generate($this->user->id);
    }

    public function notBannedUserCanSubscribeOnUser(Tester $I): void
    {
        $I->authAs($this->user);

        $notSubscribedUser = $I->grabUserThatIsNotInUserSubscriptions($this->user->id);
        $subscribeUrl = sprintf(self::USER_SUBSCRIBE_URI_TEMPLATE, $notSubscribedUser->id);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxGetRequest(trim($subscribeUrl, '\''));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['state' => true]);
    }

    public function notBannedUserCanUnsubscribeFromUser(Tester $I): void
    {
        $I->authAs($this->user);

        $subscribedUser = $I->grabUserThatIsInUserSubscription($this->user->id);
        $unsubscribeUrl = sprintf(self::USER_UNSUBSCRIBE_URI_TEMPLATE, $subscribedUser->id);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxGetRequest(trim($unsubscribeUrl, '\''));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['state' => false]);
    }

    public function subscribe(Tester $I): void
    {
        $this->setUserSubscriptionState($I, $this->user->id, false);
        $I->amOnPage("/subscribe/on/{$this->user->id}/$this->hash");
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Поздравляем! Вы успешно подписались на еженедельную рассылку лучших материалов сайта.');
        $I->assertTrue($this->getUserSubscriptionState($I, $this->user->id));
    }

    public function unsubscribe(Tester $I): void
    {
        $this->setUserSubscriptionState($I, $this->user->id, true);
        $I->amOnPage("/subscribe/off/{$this->user->id}/$this->hash");
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Вы отписались от еженедельной рассылки.');
        $I->see('Подписаться на рассылку');
        $I->assertFalse($this->getUserSubscriptionState($I, $this->user->id));
    }

    public function unsubscribeFromNotification(Tester $I): void
    {
        $I->amOnPage("/subscribe/notifications/off/{$this->user->id}/$this->hash");
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Вы можете отписаться от рассылки уведомлений');
    }

    /**
     * @dataProvider getInvalidSubscribeUrl
     */
    public function invalidRequestForProcess(Tester $I, Example $example): void
    {
        $I->wantTo($example['description']);
        $I->amOnPage(sprintf($example['url'], $this->user->id));
        $I->seeAlert('error', 'Не удалось изменить параметры вашего профиля.');
    }

    public function getInvalidSubscribeUrl(): \Generator
    {
        yield [
            'description' => 'valid user id and invalid hash for subscription',
            'url' => '/subscribe/on/%s/invalidHash/',
        ];

        yield [
            'description' => 'valid user id and invalid hash for unsubscription',
            'url' => '/subscribe/off/%s/invalidHash/',
        ];

        yield [
            'description' => 'invalid hash and user id for subscription',
            'url' => '/subscribe/on/99999/invalidHash/',
        ];

        yield [
            'description' => 'invalid hash and user id for unsubscription',
            'url' => '/subscribe/off/99999/invalidHash/',
        ];

        yield [
            'description' => 'invalid hash and user id for unsubscription notification',
            'url' => '/subscribe/notifications/off/99999/invalidHash',
        ];
    }

    private function getUserSubscriptionState(Tester $I, int $userId): bool
    {
        return (bool) $I->grabFromDatabase('users', 'is_subscribed_to_weekly_newsletter', [
            'id' => $userId,
        ]);
    }

    private function setUserSubscriptionState(Tester $I, int $userId, bool $state): void
    {
        $I->updateInDatabase('users', [
            'is_subscribed_to_weekly_newsletter' => $state ? 1 : 0,
        ], [
            'id' => $userId,
        ]);
    }
}

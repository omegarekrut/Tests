<?php

namespace Tests\Acceptance\Company;

use Page\AccessCheckPages;
use Tester;

/**
 * @group company
 */
class ViewPageCest
{
    public function seeViewPage(Tester $I): void
    {
        $company = $I->grabPublicCompanyWithFilledContacts();

        $I->amOnPage(sprintf('/companies/%s/%s', $company->slug, $company->shortUuid));

        $I->see($company->name, 'title');
        $I->seeElement('link[rel="canonical"]');
        $I->seeElement('.profile-inner__row-item_title');
        $I->seeElement('.iconFS--social-telegram');
        $I->see('Контакты');
        $I->see('Адрес');
    }

    /* todo after switching from codeception, add a test confirming that the admin and moderator see the deferred entries*/

    public function notSeePublishedLaterCompanyArticle(Tester $I): void
    {
        $company = $I->grabCompanyWithNotPublishedCompanyArticle();

        $page = sprintf('/companies/%s/%s', $company->slug, $company->shortUuid);

        $testClosure = function (Tester $I) {
            $I->cantSeeElement('.author__block__icon-publish_later');
        };

        $accessCheckPages = new AccessCheckPages($I, (array) $page, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages
            ->addTest($I->findNotBannedUser(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->getAnonymousUser(), $testClosure);

        $accessCheckPages->assert();
    }

    public function subscribeToCompanyAsGuest(Tester $I): void
    {
        $company = $I->grabRandomPublicCompany();
        $I->setCookie('sessionid', '123213123123123');

        $I->amOnPage(sprintf('/subscribe-to-company/%s/', $company->shortUuid));

        $I->seeCurrentUrlEquals(sprintf('/login/?_target_path=/subscribe-to-company/%s/', $company->shortUuid));

        $I->click('Зарегистрируйтесь');
        $I->seeCurrentUrlEquals(sprintf('/users/register/?_target_path=/subscribe-to-company/%s/', $company->shortUuid));

        $login = uniqid('user_', false);

        $I->submitForm('(//form[@name="registration"])[2]', [
            'registration' => [
                'username' => $login,
                'password' => 'TEST_PASSWORD',
                'email' => $login.'@example.com',
            ],
        ], 'Зарегистрироваться');

        $email = $I->loadLastEmailMessage();
        $I->assertEquals(1, preg_match('/\/users\/confirm_email\/([\S]+)\//i', $email, $match));
        $confirmEmailUrl = $match[0];

        $I->amOnPage($confirmEmailUrl);
        $I->seeCurrentUrlEquals(sprintf('/companies/%s/%s/', $company->slug, $company->shortUuid));

        $I->canSeeElement('subscriber-widget', [':is-show-subscribe-button' => 'true']);
    }
}

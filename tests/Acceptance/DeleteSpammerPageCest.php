<?php

namespace Tests\Acceptance;

use Codeception\Scenario;
use Page\AccessCheckPages;
use Tester;
use Tests\Support\TransferObject\User;

class DeleteSpammerPageCest
{
    private $newlyActiveUser;

    public function _before(Scenario $scenario, Tester $I): void
    {
        $this->newlyActiveUser = $I->findUserByCriteria([
            'newly' => true,
            'group' => 'user',
            'emailConfirmed' => true,
            'banned' => false,
        ]);

        if (!$this->newlyActiveUser) {
            $scenario->skip('Not found newly user');
        }
    }

    public function guestCantSeeClearSpamButton(Tester $I): void
    {
        $I->amOnPage($this->getProfilePage($this->newlyActiveUser));

        $I->see($this->newlyActiveUser->username);
        $I->dontSee('Очистить спам и удалить на всегда');
    }

    public function userCantSeeClearSpamButton(Tester $I): void
    {
        $I->authAs($I->findAnotherUserInGroup($this->newlyActiveUser));
        $I->amOnPage($this->getProfilePage($this->newlyActiveUser));

        $I->dontSee('Редактировать профиль');
        $I->see($this->newlyActiveUser->username);
        $I->dontSee('Очистить спам и удалить на всегда');

        $I->logout();
    }

    public function userCantSeeClearSpamButtonOnOwnPage(Tester $I): void
    {
        $I->authAs($this->newlyActiveUser);
        $I->amOnPage($this->getProfilePage($this->newlyActiveUser));

        $I->see('Редактировать профиль');
        $I->dontSee('Очистить спам и удалить на всегда');

        $I->logout();
    }

    public function administratedUsersCanSeeClearSpam(Tester $I): void
    {
        $accessCheckPages = new AccessCheckPages($I, [$this->getProfilePage($this->newlyActiveUser)], AccessCheckPages::STRATEGY_ALLOWED);

        $testClosure = function (Tester $I) {
            $I->dontSee('Редактировать профиль');
            $I->see($this->newlyActiveUser->username);
            $I->see('Очистить спам и удалить на всегда');
        };

        $accessCheckPages
            ->addTest($I->findAdmin(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModerator(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModeratorABM(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure());

        $accessCheckPages->assert();
    }

    public function adminClearSpammer(Tester $I): void
    {
        $I->updateInDatabase('users', [
            'last_visit_ip' => $I->getFaker()->ipv4,
        ], [
            'id' => $this->newlyActiveUser->id,
        ]);

        $I->authAs($I->findAdmin());
        $I->amOnPage($this->getProfilePage($this->newlyActiveUser));

        $I->see('Очистить спам и удалить на всегда');
        $I->click('Очистить спам и удалить на всегда');

        $I->seeAlert('success', 'Все записи спаммера будут удалены. Сам спаммер будет заблокирован. Команда поставлена в очередь.');
        $I->seeInDatabase('ban_user', ['user_id' => $this->newlyActiveUser->id]);

        $I->amOnPage($this->getProfilePage($this->newlyActiveUser));
        $I->see('Заблокирован');
    }

    private function getProfilePage(User $user): string
    {
        return sprintf('/users/profile/%d/', $user->id);
    }
}

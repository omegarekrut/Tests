<?php

namespace Tests\Acceptance;

use Tester;

class ShowEmailPageCest
{
    public function canViewVisibleEmailsAsGuest(Tester $I): void
    {
        $user = $I->findNotBannedUser();
        $I->updateInDatabase('users', ['show_email' => 1], ['id' => $user->id]);

        $I->amOnPage(sprintf('/users/profile/%s/', $user->id));

        $I->see($user->username);
        $I->see($user->email);
    }

    public function cantViewHiddenEmailAsGuest(Tester $I): void
    {
        $user = $I->findNotBannedUser();
        $I->updateInDatabase('users', ['show_email' => 0], ['id' => $user->id]);

        $I->amOnPage(sprintf('/users/profile/%s/', $user->id));

        $I->see($user->username);
        $I->dontSee($user->email);
    }

    public function canViewVisibleEmailsAsUser(Tester $I): void
    {
        $user = $I->findNotBannedUser();
        $visitorUser = $I->findAnotherUserInGroup($user);
        $I->authAs($visitorUser);

        $I->updateInDatabase('users', ['show_email' => 1], ['id' => $user->id]);

        $I->amOnPage(sprintf('/users/profile/%s/', $user->id));

        $I->see($user->username);
        $I->see($user->email);
    }

    public function cantViewHiddenEmailAsUser(Tester $I): void
    {
        $user = $I->findNotBannedUser();
        $I->updateInDatabase('users', ['show_email' => 0], ['id' => $user->id]);

        $I->amOnPage(sprintf('/users/profile/%s/', $user->id));

        $I->see($user->username);
        $I->dontSee($user->email);

        $I->logout();
    }

    public function canViewEmailOnOwnPage(Tester $I): void
    {
        $user = $I->findNotBannedUser();
        $I->authAs($user);

        $I->amOnPage('/users/profile/');

        $I->see($user->username);
        $I->see($user->email);

        $I->logout();
    }

    public function canViewVisibleEmailsAsAdmin(Tester $I): void
    {
        $user = $I->findNotBannedUser();
        $adminUser = $I->findAdmin();
        $I->authAs($adminUser);

        $I->updateInDatabase('users', ['show_email' => 1], ['id' => $user->id]);
        $I->amOnPage(sprintf('/users/profile/%s/', $user->id));

        $I->see($user->username);
        $I->see($user->email);

        $I->logout();
    }

    public function canViewHiddenEmailsAsAdmin(Tester $I): void
    {
        $user = $I->findNotBannedUser();
        $adminUser = $I->findAdmin();
        $I->authAs($adminUser);

        $I->updateInDatabase('users', ['show_email' => 0], ['id' => $user->id]);
        $I->amOnPage(sprintf('/users/profile/%s/', $user->id));

        $I->see($user->username);
        $I->see($user->email);

        $I->logout();
    }
}

<?php

use Codeception\Actor;
use Tests\Support\TransferObject\User;

class Tester extends Actor
{
    use _generated\TesterActions;

    private $authorized = false;

    public function getAnonymousUser(): User
    {
        return new User();
    }

    public function authAs(User $user, bool $remember = false): void
    {
        if ($this->isAuthorized()) {
            $this->logout();
        }

        $currentUrl = $this->getCurrentUrl();

        if ($currentUrl === null || preg_match('/\/admin/', $currentUrl)) {
            $this->amOnPage('/login/');
        }

        $this->fillField('login[login]', $user->username);
        $this->fillField('login[password]', $user->password);

        if ($remember === false) {
            $this->uncheckOption('[name="login[remember]"]');
        } else {
            $this->checkOption('[name="login[remember]"]');
        }

        $this->click('Войти', '#authSubmit');

        $this->authorized = true;
    }

    public function isAuthorized(): bool
    {
        return $this->authorized;
    }

    public function logout(): void
    {
        if (!$this->authorized) {
            return;
        }
        $this->amOnPage('/logout/');
        $this->authorized = false;
    }

    /** Test methods **/

    /**
     * @todo move to module and refactor
     */
    public function seeAlert(string $type, string $message): void
    {
        $alerts = $this->findAlertsInPageSource($this->grabPageSource());
        $actualAlert = null;

        foreach ($alerts as $alert) {
            if ($alert['level'] !== $type || $alert['message'] !== $message) {
                continue;
            }

            $actualAlert = $alert;

            break;
        }

        $failedMessage = sprintf('Alert not found in %s.', json_encode($alerts));

        $this->assertNotEmpty($actualAlert, $failedMessage);
    }

    public function seeRegExp(string $pattern): void
    {
        $this->assertRegExp($pattern, $this->grabPageSource());
    }

    /**
     * Увидеть правильную сортировку записей
     *
     * @param string[] $variables Список переменных для сравнения
     * @param integer[] $ids Список id записей
     */
    public function seeOrderDescRecord(array $variables, array $ids)
    {
        for ($i = 1; $i < count($variables); $i++) {
            $assertTrue = $variables[$i] <= $variables[$i - 1];

            if ($assertTrue !== true) {
                $prevId = $ids[$i - 1];
                $currentId = $ids[$i];

                $prevPriority = $this->grabFromDatabase('records', 'priority', [
                    'id' => $prevId,
                ]);
                $currentPriority = $this->grabFromDatabase('records', 'priority', [
                    'id' => $currentId,
                ]);

                $assertTrue = $prevPriority > $currentPriority;
            }

            $this->assertTrue($assertTrue);
        }
    }

    private function findAlertsInPageSource(string $pageSource): array
    {
        if (!preg_match_all('/var alertsBag = (\[)(.*)(\])/i', $pageSource, $matching, PREG_SET_ORDER)) {
            return [];
        }

        $lastAlertsDefinitionMatching = end($matching);

        return json_decode($lastAlertsDefinitionMatching[1].$lastAlertsDefinitionMatching[2].$lastAlertsDefinitionMatching[3], true);
    }
}

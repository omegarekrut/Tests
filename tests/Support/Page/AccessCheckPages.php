<?php

namespace Page;

use Codeception\Util\HttpCode;
use Closure;
use Generator;
use Tester;
use Tests\Support\TransferObject\User;
use InvalidArgumentException;

class AccessCheckPages
{
    const STRATEGY_ALLOWED = 'Allowed';
    const STRATEGY_DENIED = 'Denied';
    const STRATEGY_NOT_FOUND = 'NotFound';
    const STRATEGY_FORBIDDEN = 'Forbidden';

    private $tester;
    private $pageUris;
    private $testsConfig;
    private $strategy;

    public function __construct(Tester $I, array $pageUris, $strategy = self::STRATEGY_ALLOWED)
    {
        if (!in_array($strategy, self::getAllowedStrategies())) {
            throw new InvalidArgumentException(sprintf(
                'Допустимы только стратегии %s',
                implode(', ', self::getAllowedStrategies())
            ));
        }

        $this->tester = $I;
        $this->pageUris = $pageUris;
        $this->strategy = $strategy;
    }

    public function addTest(User $user, Closure $testClosure = null, Closure $beforeTestClosure = null, Closure $afterTestClosure = null): self
    {
        $this->testsConfig[] = [
            $user,
            $beforeTestClosure,
            $testClosure,
            $afterTestClosure,
        ];

        return $this;
    }

    public function addTestWithAuth(User $user, Closure $beforeTestClosure = null): self
    {
        return $this->addTest(
            $user,
            $beforeTestClosure,
            $this->getLoginClosure(),
            $this->getLogoutClosure()
        );
    }

    public function assert(): void
    {
        if (empty($this->testsConfig)) {
            throw new InvalidArgumentException('Не добавлено ни одного теста. Используйте $accessCheckPages->addTest();');
        }

        $this->renderTestCoverage();
        call_user_func([$this, 'assert'.$this->strategy]);
    }

    public function getLoginClosure(): Closure
    {
        return function (Tester $I, User $user) {
            $I->authAs($user);
        };
    }

    public function getLogoutClosure(): Closure
    {
        return function (Tester $I, User $user) {
            $I->logout();
        };
    }

    private static function getAllowedStrategies(): array
    {
        return [
            self::STRATEGY_ALLOWED,
            self::STRATEGY_DENIED,
            self::STRATEGY_NOT_FOUND,
            self::STRATEGY_FORBIDDEN,
        ];
    }

    /**
     * Для информативности в консоли, меняется сообщение о том что бует сделано
     * Если тест отвалится раньше, будет указана конкретная страница и группа тестера
     */
    private function renderTestCoverage(): void
    {
        $this->tester->wantTo(sprintf(
            'Check %s access to \'%s\'',
            $this->strategy,
            implode('\', \'', $this->pageUris)
        ));
    }

    private function assertAllowed(): void
    {
        foreach ($this->stepIterator() as $item) {
            [$user, $pageUri] = $item;

            $this->tester->comment(sprintf('Check allow access to %s by %s', $pageUri, $user->group));
            $this->tester->amOnPage($pageUri);
            $this->tester->seeResponseCodeIs(HttpCode::OK);
        }
    }

    protected function stepIterator(): Generator
    {
        foreach ($this->testsConfig as $testerConfig) {
            [$user, $beforeTestClosure, $testClosure, $afterTestClosure] = $testerConfig;

            $this->callClosure($user, $beforeTestClosure);

            foreach ($this->pageUris as $pageUri) {
                yield [
                    $user,
                    $pageUri,
                ];
            }

            $this->callClosure($user, $testClosure);
            $this->callClosure($user, $afterTestClosure);
        }
    }

    private function callClosure(User $user, Closure $closure = null): void
    {
        if ($closure === null) {
            return;
        }

        $closure($this->tester, $user);
    }

    private function assertNotFound(): void
    {
        foreach ($this->stepIterator() as $item) {
            [$user, $pageUri] = $item;

            $this->tester->comment(sprintf('Check deny access to "%s" as "%s"', $pageUri, $user->group));
            $this->tester->amOnPage($pageUri);
            $this->tester->see('Страница не найдена');
        }
    }

    private function assertDenied(): void
    {
        foreach ($this->stepIterator() as $item) {
            [$user, $pageUri] = $item;

            $this->tester->comment(sprintf('Check deny access to "%s" as "%s"', $pageUri, $user->group));
            $this->tester->amOnPage($pageUri);
            $this->tester->see('Доступ запрещен');
        }
    }

    private function assertForbidden(): void
    {
        foreach ($this->stepIterator() as $item) {
            [$user, $pageUri] = $item;

            $this->tester->comment(sprintf('Check deny access to "%s" as "%s"', $pageUri, $user->group));
            $this->tester->amOnPage($pageUri);
            $this->tester->seeResponseCodeIs(HttpCode::FORBIDDEN);
        }
    }
}

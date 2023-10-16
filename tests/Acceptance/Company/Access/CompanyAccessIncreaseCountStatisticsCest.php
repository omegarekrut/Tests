<?php

namespace Tests\Acceptance\Company\Access;

use App\Domain\Company\Entity\Statistics\ValueObject\StatisticsType;
use Codeception\Util\HttpCode;
use Tester;
use Tests\Support\TransferObject\User;

class CompanyAccessIncreaseCountStatisticsCest
{
    public function allowAccess(Tester $I): void
    {
        $company = $I->grabRandomPublicCompany();
        $I->amOnPage('/companies/');

        foreach ($this->getStatisticsType() as $statisticsType) {
            $companyStatisticsUrl = sprintf(
                '/companies/%s/%s/increase-count/%s/',
                $company->slug,
                $company->shortUuid,
                $statisticsType,
            );

            foreach ($this->getUsersOfDifferentGroups($I) as $user) {
                $I->amOnPage('/companies/');
                $I->authAs($user);
                $I->sendPost($companyStatisticsUrl);
                $I->seeResponseCodeIs(HttpCode::OK);
                $I->logout();
            }
        }
    }

    /**
     * @return string[]
     */
    private function getStatisticsType(): array
    {
        $data = [];

        foreach (StatisticsType::toArray() as $nameOfStatisticsType) {
            $data[] = $nameOfStatisticsType;
        }

        return $data;
    }

    /**
     * @return User[]
     */
    private function getUsersOfDifferentGroups(Tester $I): array
    {
        $users = [];

        $users[] = $I->findAdmin();
        $users[] = $I->findModerator();
        $users[] = $I->findModeratorABM();
        $users[] = $I->findNotBannedUser();
        $users[] = $I->getAnonymousUser();

        return $users;
    }
}

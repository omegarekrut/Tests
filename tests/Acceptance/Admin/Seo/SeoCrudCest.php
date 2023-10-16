<?php

namespace Tests\Acceptance\Admin\Seo;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group seo
 */
class SeoCrudCest
{
    /** @var string|null */
    private $seoUri;

    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/seo/');
    }

    public function seeSeoRulesList(Tester $I): void
    {
        $I->see('Переопределенные сео данные по адресам');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function createSeoRule(Tester $I): void
    {
        $I->amOnPage('/admin/seo/');
        $I->click('Добавить uri');
        $I->see('Добавление seo uri');

        $this->seoUri = sprintf('/%s/', $I->getFaker()->slug());
        $I->fillField('seo_data[uri]', $this->seoUri);
        $I->fillField('seo_data[title]', $I->getFaker()->realText(50));

        $I->click('Сохранить');

        $I->seeAlert('success', 'Правило успешно сохранено');
    }

    /**
     * @depends createSeoRule
     */
    public function findAndEditSeoRule(Tester $I): void
    {
        $I->fillField('seo_data_search[uri]', $this->seoUri);
        $I->click('//button[@type="submit"]');
        $I->see($this->seoUri);

        $I->click('//a[@title="Изменить"]');
        $I->see('Редактирование uri');

        $this->seoUri = sprintf('/%s/', $I->getFaker()->slug());
        $I->fillField('seo_data[uri]', $this->seoUri);

        $I->click('Сохранить');

        $I->seeAlert('success', 'Seo uri изменен успешно');
    }

    /**
     * @depends findAndEditSeoRule
     */
    public function findAndDeleteSeoRule(Tester $I): void
    {
        $I->fillField('seo_data_search[uri]', $this->seoUri);
        $I->click('//button[@type="submit"]');
        $I->see($this->seoUri);

        $seoUriId = current($I->grabColumnFromDatabase('seo_data', 'id', ['uri' => $this->seoUri]));

        $I->amOnPage(sprintf('/admin/seo/%s/delete/', $seoUriId));
        $I->amOnPage('/admin/seo/');
        $I->fillField('seo_data_search[uri]', $this->seoUri);
        $I->click('//button[@type="submit"]');

        $I->dontSeeInSource(sprintf('>%s<', $this->seoUri));
    }
}

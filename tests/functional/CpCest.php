<?php

namespace simplonprod\googlerecaptchatests\functional;

use Craft;
use craft\elements\User;
use FunctionalTester;
use simplonprod\newsletter\adapters\Mailjet;

class CpCest
{
    /**
     * @var string
     */
    public $cpTrigger;

    /**
     * @var
     */
    public $currentUser;


    public function _before(FunctionalTester $I)
    {
        $this->currentUser = User::find()
            ->admin()
            ->one();

        $I->amLoggedInAs($this->currentUser);
        $this->cpTrigger = Craft::$app->getConfig()->getGeneral()->cpTrigger;

        Craft::$app->setEdition(Craft::Pro);
    }

    // tests
    public function tryToShowControlPanel(FunctionalTester $I)
    {
        Craft::$app->language = 'fr-FR';
        $I->amOnPage('/' . $this->cpTrigger . '/settings/plugins/google-recaptcha');
        $I->seeResponseCodeIs(200);
        $I->see('Version d’API');
        $I->see('Clé du site');
        $I->see('Clé secrète');
    }

    public function tryToSaveSettingFromControlPanel(FunctionalTester $I)
    {
        $I->amOnPage('/' . $this->cpTrigger . '/settings/plugins/google-recaptcha');
        $I->submitForm('#main-form', [
            'settings' => [
                'version'   => 3,
                'siteKey'   => '$RECAPTCHA_SITE_KEY',
                'secretKey' => '$RECAPTCHA_SECRET_KEY'
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->see('Plugin settings saved');
        $I->seeInDatabase('projectconfig', ['path' => 'plugins.google-recaptcha.settings.version', 'value' => '"3"']);
    }
}

<?php

namespace juban\googlerecaptchatests\functional;

use Craft;
use craft\elements\User;
use FunctionalTester;
use juban\googlerecaptcha\GoogleRecaptcha;

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


    public function _before(FunctionalTester $I): void
    {
        $this->currentUser = User::find()
            ->admin()
            ->one();

        $I->amLoggedInAs($this->currentUser);
        $this->cpTrigger = Craft::$app->getConfig()->getGeneral()->cpTrigger;

        Craft::$app->setEdition(Craft::Pro);
    }

    // tests
    public function tryToShowV2ControlPanel(FunctionalTester $I): void
    {
        Craft::$app->language = 'fr-FR';
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 2,
            'siteKey' => 'some-site-key',
            'secretKey' => 'some-secret-key',
            'size' => 'normal',
            'theme' => 'light',
            'badge' => 'bottomright',
        ]);
        $I->amOnPage('/' . $this->cpTrigger . '/settings/plugins/google-recaptcha');
        $I->seeResponseCodeIs(200);
        $I->see('v2', '#settings-version > option:selected');
        $I->see('Version d’API','label');
        $I->see('Clé du site','label');
        $I->see('Clé secrète','label');
        $I->see('Taille','label');
        $I->see('Thème','label');
        $I->see('Badge','label');
    }

    public function tryToShowV3ControlPanel(FunctionalTester $I): void
    {
        Craft::$app->language = 'fr-FR';
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 3,
            'siteKey' => 'some-site-key',
            'secretKey' => 'some-secret-key',
        ]);
        $I->amOnPage('/' . $this->cpTrigger . '/settings/plugins/google-recaptcha');
        $I->seeResponseCodeIs(200);
        $I->see('v3', '#settings-version > option:selected');
        $I->see('Version d’API','label');
        $I->see('Clé du site', 'label');
        $I->see('Clé secrète', 'label');
        $I->see('Action par défaut', 'label');
        $I->see('Seuil de score par défaut', 'label');
        $I->see('Paramètres Actions', 'label');
        $I->see('Ajouter une action', 'button');
    }

    public function tryToSaveSettingFromControlPanel(FunctionalTester $I): void
    {
        $I->amOnPage('/' . $this->cpTrigger . '/settings/plugins/google-recaptcha');
        $I->submitForm('#main-form', [
            'settings' => [
                'version' => 3,
                'siteKey' => '$RECAPTCHA_SITE_KEY',
                'secretKey' => '$RECAPTCHA_SECRET_KEY',
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeCurrentUrlMatches('/admin\/settings$/');
        $I->seeInDatabase('projectconfig', ['path' => 'plugins.google-recaptcha.settings.version', 'value' => '"3"']);
    }
}

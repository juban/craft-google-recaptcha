<?php
/**
 * Google Recaptcha plugin for Craft CMS 3.x
 *
 * @link      https://www.simplonprod.co
 * @copyright Copyright (c) 2021 Simplon.Prod
 */

namespace simplonprod\googlerecaptcha;

use Craft;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use craft\web\twig\variables\CraftVariable;
use simplonprod\googlerecaptcha\models\Settings;
use simplonprod\googlerecaptcha\services\Recaptcha as RecaptchaService;
use simplonprod\googlerecaptcha\variables\GoogleRecaptchaVariable;
use yii\base\Event;

/**
 * @author    Simplon.Prod
 * @package   GoogleRecaptcha
 * @since     1.0.0
 *
 * @property  RecaptchaService $recaptcha
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class GoogleRecaptcha extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * GoogleRecaptcha::$plugin
     *
     * @var GoogleRecaptcha
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    public $schemaVersion = '1.0.0';

    public $hasCpSettings = true;

    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @return void
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->_registerTwigVariables();
        $this->_registerAfterInstall();

        Craft::info(
            Craft::t(
                'google-recaptcha',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    private function _registerTwigVariables()
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('googleRecaptcha', GoogleRecaptchaVariable::class);
            }
        );
    }

    private function _registerAfterInstall()
    {
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function(PluginEvent $event) {
                if ($event->plugin === $this && Craft::$app->getRequest()->getIsCpRequest()) {
                    // Redirect to settings page
                    Craft::$app->getResponse()->redirect(
                        UrlHelper::cpUrl('settings/plugins/google-recaptcha')
                    )->send();
                }
            }
        );
    }

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        $configService = Craft::$app->getConfig();
        $config = $configService->getConfigFromFile('google-recaptcha');
        if (!empty($config)) {
            $configPath = $configService->getConfigFilePath('google-recaptcha');
        }
        return Craft::$app->view->renderTemplate(
            'google-recaptcha/settings',
            [
                'settings' => $this->getSettings(),
                'configPath' => $configPath ?? null,
            ]
        );
    }
}

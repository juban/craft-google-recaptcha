<?php
/**
 * Newsletter plugin for Craft CMS 3.x
 *
 * Craft CMS Newsletter plugin
 *
 * @link      https://www.simplonprod.co
 * @copyright Copyright (c) 2021 Simplon.Prod
 */

namespace simplonprod\googlerecaptchatests\unit;

use Craft;
use simplonprod\googlerecaptcha\GoogleRecaptcha;
use UnitTester;

/**
 * @author    Simplon.Prod
 * @package   Google reCAPTCHA
 * @since     1.0.0
 */
class PluginUnitTest extends BaseUnitTest
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     *
     */
    public function testPluginInstance()
    {
        $this->assertInstanceOf(
            GoogleRecaptcha::class,
            GoogleRecaptcha::$plugin
        );
    }

    /**
     *
     */
    public function testPluginInstallation()
    {
        $this->assertNull(Craft::$app->getPlugins()->getPlugin(self::PLUGIN_HANDLE)->uninstall());
        $this->assertNull(Craft::$app->getPlugins()->getPlugin(self::PLUGIN_HANDLE)->install());
    }

    /**
     *
     */
    public function testCraftEdition()
    {
        Craft::$app->setEdition(Craft::Pro);

        $this->assertSame(
            Craft::Pro,
            Craft::$app->getEdition()
        );
    }
}

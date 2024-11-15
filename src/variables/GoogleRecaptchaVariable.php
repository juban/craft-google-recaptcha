<?php
/**
 * Google Recaptcha plugin for Craft CMS 3.x
 *
 * Google Recaptcha for Craft CMS
 *
 * @link      https://github.com/juban
 * @copyright Copyright (c) 2022 juban
 */

namespace juban\googlerecaptcha\variables;

use Craft;
use craft\helpers\App;
use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\web\View;
use juban\googlerecaptcha\GoogleRecaptcha;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Google Recaptcha Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.googleRecaptcha }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    juban
 * @package   GoogleRecaptcha
 * @since     1.0.0
 */
class GoogleRecaptchaVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Render the reCAPTCHA widget
     * {{ craft.googleRecaptcha.render(options, instantRender) }}
     *
     * @param array $options
     * @param bool $instantRender
     * @return Markup
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function render(array $options = [], bool $instantRender = false): Markup
    {
        $recaptchaTag = '';
        $settings = GoogleRecaptcha::$plugin->getSettings();

        $id = $options['id'] ?? 'recaptcha-' . StringHelper::randomString(6);
        ArrayHelper::remove($options, 'id');

        $siteKey = App::parseEnv($settings->siteKey);

        $scriptAttributes = isset($options['scriptOptions']) ? Html::renderTagAttributes(
            $options['scriptOptions']
        ) : '';
        ArrayHelper::remove($options, 'scriptOptions');

        if ((int) App::parseEnv($settings->version) === 3) {
            $action = $options['action'] ?? $settings->actionName;
            ArrayHelper::remove($options, 'action');
            $formId = $options['formId'] ?? null;
            ArrayHelper::remove($options, 'formId');
            $recaptchaTag = self::_getV3Tag($id, $siteKey, $options, $scriptAttributes, $action, $formId);
        } else {
            $recaptchaTag = self::_getV2Tag(
                $id,
                $siteKey,
                $options,
                $scriptAttributes,
                App::parseEnv($settings->size),
                App::parseEnv($settings->theme),
                App::parseEnv($settings->badge),
                $instantRender
            );
        }

        return Template::raw($recaptchaTag);
    }

    /**
     * @param string $id
     * @param string $siteKey
     * @param array $options
     * @param string $scriptAttributes
     * @param string $action
     * @param string|null $formId
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws InvalidConfigException
     */
    private static function _getV3Tag(
        string $id,
        string $siteKey,
        array $options,
        string $scriptAttributes,
        string $action,
        string $formId = null,
    ): string {
        return Craft::$app->getView()->renderTemplate('google-recaptcha/tags/v3', [
            'id' => $id,
            'action' => $action,
            'formId' => $formId,
            'hiddenInput' => Html::hiddenInput('g-recaptcha-response', '', ArrayHelper::merge($options, ['id' => $id]))
                . Html::hiddenInput('g-recaptcha-action', Craft::$app->getSecurity()->hashData($action)),
            'scriptAttributes' => $scriptAttributes,
            'siteKey' => $siteKey,
        ], View::TEMPLATE_MODE_CP);
    }

    /**
     * @param string $id
     * @param string $siteKey
     * @param array $options
     * @param string $scriptAttributes
     * @param string $size
     * @param string $theme
     * @param string $badge
     * @param bool $instantRender
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private static function _getV2Tag(
        string $id,
        string $siteKey,
        array $options,
        string $scriptAttributes,
        string $size,
        string $theme,
        string $badge,
        bool $instantRender,
    ): string {
        return Craft::$app->getView()->renderTemplate('google-recaptcha/tags/v2', [
            'callbackName' => StringHelper::camelCase($id),
            'id' => $id,
            'div' => Html::tag('div', '', ArrayHelper::merge($options, ['id' => $id])),
            'scriptAttributes' => $scriptAttributes,
            'siteKey' => $siteKey,
            'size' => $size,
            'theme' => $theme,
            'badge' => $badge,
            'instantRender' => $instantRender,
        ], View::TEMPLATE_MODE_CP);
    }

    /**
     * Return the version setting
     * @return string
     */
    public function getVersion(): string
    {
        return GoogleRecaptcha::$plugin->getSettings()->version;
    }

    /**
     * Return the parsed siteKey setting
     * @return string
     */
    public function getSiteKey(): string
    {
        return App::parseEnv(GoogleRecaptcha::$plugin->getSettings()->siteKey);
    }
}

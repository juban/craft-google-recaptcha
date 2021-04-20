<?php
/**
 * Google Recaptcha plugin for Craft CMS 3.x
 *
 * Google Recaptcha for Craft CMS
 *
 * @link      https://www.simplonprod.co
 * @copyright Copyright (c) 2021 Simplon.Prod
 */

namespace simplonprod\googlerecaptcha\models;

use Craft;
use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;

/**
 * GoogleRecaptcha Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Simplon.Prod
 * @package   GoogleRecaptcha
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $version = 2;
    public $siteKey;
    public $secretKey;
    public $size;
    public $theme;
    public $badge;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['parser'] = [
            'class'      => EnvAttributeParserBehavior::class,
            'attributes' => [
                'siteKey',
                'secretKey'
            ],
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['version', 'siteKey', 'secretKey'], 'required'];
        $rules[] = [
            ['size', 'theme', 'badge'],
            'required',
            'when' => function ($model) {
                return $model->version === 2;
            }
        ];
        $rules[] = ['version', 'in', 'range' => array_keys(self::getVersionOptions())];
        $rules[] = ['size', 'in', 'range' => array_keys(self::getSizeOptions()), 'skipOnEmpty' => true];
        $rules[] = ['theme', 'in', 'range' => array_keys(self::getThemeOptions()), 'skipOnEmpty' => true];
        $rules[] = ['badge', 'in', 'range' => array_keys(self::getBadgeOptions()), 'skipOnEmpty' => true];

        return $rules;
    }

    public static function getVersionOptions(): array
    {
        return [
            3 => 'v3',
            2 => 'v2'
        ];
    }

    public static function getSizeOptions(): array
    {
        return [
            'normal'    => Craft::t('google-recaptcha', 'Normal'),
            'compact'   => Craft::t('google-recaptcha', 'Compact'),
            'invisible' => Craft::t('google-recaptcha', 'Invisible')
        ];
    }

    public static function getThemeOptions(): array
    {
        return [
            'light' => Craft::t('google-recaptcha', 'Light'),
            'dark'  => Craft::t('google-recaptcha', 'Dark')
        ];
    }

    public static function getBadgeOptions(): array
    {
        return [
            'bottomright' => Craft::t('google-recaptcha', 'Bottom right'),
            'bottomleft'  => Craft::t('google-recaptcha', 'Bottom left'),
            'inline'      => Craft::t('google-recaptcha', 'Inline')
        ];
    }
}

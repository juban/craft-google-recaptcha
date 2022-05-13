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
use craft\helpers\ArrayHelper;
use yii\base\DynamicModel;

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
    public $actionName = 'homepage';
    public $scoreThreshold;
    public $actions = [];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['parser'] = [
            'class' => EnvAttributeParserBehavior::class,
            'attributes' => [
                'siteKey',
                'secretKey',
                'version',
                'size',
                'theme',
                'badge',
                'actionName',
                'scoreThreshold',
            ],
        ];
        return $behaviors;
    }

    /**
     * Validates the actions.
     */
    public function validateActions()
    {
        $hasErrors = false;
        foreach ($this->actions as &$action) {
            $model = DynamicModel::validateData($action, [
                [['name', 'scoreThreshold'], 'required'],
                ['name', 'trim'],
                ['name', 'match', 'pattern' => '/^[\w\/]+$/'],
                ['scoreThreshold', 'double', 'min' => 0, 'max' => 1],
            ]);
            $action = $model;
            if ($model->hasErrors('name')) {
                $action['name'] = ['value' => $action['name'], 'hasErrors' => true];
                $hasErrors = true;
            }
            if ($model->hasErrors('scoreThreshold')) {
                $action['scoreThreshold'] = ['value' => $action['scoreThreshold'], 'hasErrors' => true];
                $hasErrors = true;
            }
        }
        unset($action);

        if ($hasErrors) {
            $this->addError('actions', Craft::t('google-recaptcha', 'Some actions values are incorrect.'));
        }
    }

    /**
     * Return an indexed array with actions as keys and score threshold as values
     * @return array
     */
    public function getScoreThresholdPerAction(): array
    {
        if (is_array($this->actions)) {
            return ArrayHelper::map($this->actions, 'name', 'scoreThreshold');
        }
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = ['scoreThreshold', 'default', 'value' => null];
        $rules[] = ['actionName', 'default', 'value' => 'homepage'];
        $rules[] = [['version', 'siteKey', 'secretKey'], 'required'];
        $rules[] = [
            ['size', 'theme', 'badge'],
            'required',
            'when' => function($model) {
                return ((int)$model->version) === 2;
            },
        ];
        $rules[] = ['version', 'in', 'range' => array_keys(self::getVersionOptions())];
        $rules[] = ['size', 'in', 'range' => array_keys(self::getSizeOptions()), 'skipOnEmpty' => true];
        $rules[] = ['theme', 'in', 'range' => array_keys(self::getThemeOptions()), 'skipOnEmpty' => true];
        $rules[] = ['badge', 'in', 'range' => array_keys(self::getBadgeOptions()), 'skipOnEmpty' => true];
        $rules[] = [
            'actionName',
            'match',
            'pattern' => '/^[\w\/]+$/',
            'when' => function($model) {
                return ((int)$model->version) === 3;
            },
        ];
        $rules[] = ['scoreThreshold', 'double', 'min' => 0, 'max' => 1, 'skipOnEmpty' => true];
        $rules[] = [
            'actions',
            'validateActions',
            'skipOnEmpty' => true,
            'when' => function($model) {
                return ((int)$model->version) === 3;
            },
        ];
        return $rules;
    }

    public static function getVersionOptions(): array
    {
        return [
            3 => 'v3',
            2 => 'v2',
        ];
    }

    public static function getSizeOptions(): array
    {
        return [
            'normal' => Craft::t('google-recaptcha', 'Normal'),
            'compact' => Craft::t('google-recaptcha', 'Compact'),
            'invisible' => Craft::t('google-recaptcha', 'Invisible'),
        ];
    }

    public static function getThemeOptions(): array
    {
        return [
            'light' => Craft::t('google-recaptcha', 'Light'),
            'dark' => Craft::t('google-recaptcha', 'Dark'),
        ];
    }

    public static function getBadgeOptions(): array
    {
        return [
            'bottomright' => Craft::t('google-recaptcha', 'Bottom right'),
            'bottomleft' => Craft::t('google-recaptcha', 'Bottom left'),
            'inline' => Craft::t('google-recaptcha', 'Inline'),
        ];
    }
}

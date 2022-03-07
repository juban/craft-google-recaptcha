<?php
/**
 * Google Recaptcha plugin for Craft CMS 3.x
 *
 * Google Recaptcha for Craft CMS
 *
 * @link      https://www.simplonprod.co
 * @copyright Copyright (c) 2021 Simplon.Prod
 */

namespace simplonprod\googlerecaptcha\services;

use Craft;
use craft\base\Component;
use craft\helpers\App;
use craft\helpers\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use simplonprod\googlerecaptcha\GoogleRecaptcha;
use yii\helpers\VarDumper;
use yii\web\ForbiddenHttpException;

/**
 * Recaptcha Service
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Simplon.Prod
 * @package   GoogleRecaptcha
 * @since     1.0.0
 *
 * @property-read \GuzzleHttp\Client $recaptchaClient
 */
class Recaptcha extends Component
{
    // Public Methods
    // =========================================================================

    public function verify(): bool
    {
        $request = Craft::$app->getRequest();
        $recaptchaResponse = $request->getParam('g-recaptcha-response');
        if ($recaptchaResponse === null) {
            throw new ForbiddenHttpException('Invalid reCAPTCHA response');
        }

        // Validate posted action (if any)
        $recaptchaAction = Craft::$app->getSecurity()->validateData($request->getBodyParam('g-recaptcha-action'));

        $result = '';

        $client = $this->getRecaptchaClient();
        $settings = GoogleRecaptcha::$plugin->getSettings();
        $params = [
            'secret'   => App::parseEnv($settings->secretKey),
            'response' => $recaptchaResponse,
            'remoteip' => $request->getUserIP(),
        ];
        try {
            Craft::debug("Checking reCAPTCHA response", __METHOD__);
            $response = $client->request('POST', 'siteverify', [
                'form_params' => $params
            ]);
            if ($response->getStatusCode() == 200) {
                $result = Json::decodeIfJson($response->getBody());
            }
        } catch (ConnectException $e) {
            Craft::warning($e->getMessage(), __METHOD__);
            return false;
        }

        if (empty($result['success'])) {
            Craft::warning("reCAPTCHA check failed: " . VarDumper::dumpAsString($result), __METHOD__);
            return false;
        }

        if (!empty($result['action'])) {
            if ((!empty($result['action']) && $result['action'] != $recaptchaAction)) {
                Craft::warning("reCAPTCHA check failed: " . VarDumper::dumpAsString($result), __METHOD__);
                return false;
            }

            if (isset($result['score'])) {
                $scoreThresholdPerAction = $settings->getScoreThresholdPerAction();
                $scoreThreshold = $scoreThresholdPerAction[$result['action']] ?? App::parseEnv($settings->scoreThreshold);
                if ($scoreThreshold !== null && (double)$result['score'] < (double)$scoreThreshold) {
                    Craft::warning("reCAPTCHA score checking failed: " . $result['score'], __METHOD__);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return Client
     * @codeCoverageIgnore
     */
    public function getRecaptchaClient(): Client
    {
        return new Client([
            'base_uri'        => 'https://www.google.com/recaptcha/api/',
            'timeout'         => 5,
            'connect_timeout' => 5,
        ]);
    }
}

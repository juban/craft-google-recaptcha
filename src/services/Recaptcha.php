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
use craft\helpers\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use simplonprod\googlerecaptcha\GoogleRecaptcha;
use yii\web\ForbiddenHttpException;

/**
 * Recaptcha Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Simplon.Prod
 * @package   GoogleRecaptcha
 * @since     1.0.0
 */
class Recaptcha extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Verify the posted recaptcha token against Google reCAPTCHA API
     *
     *     GoogleRecaptcha::$plugin->recaptcha->verify()
     *
     * @return mixed
     */
    public function verify()
    {
        $request = Craft::$app->getRequest();
        $recaptchaResponse = $request->getParam('g-recaptcha-response');
        if ($recaptchaResponse === null) {
            throw new ForbiddenHttpException('Invalid reCAPTCHA response');
        }

        $result = '';

        $client = $this->getRecaptchaClient();
        $settings = GoogleRecaptcha::$plugin->getSettings();
        $params = [
            'secret' =>  Craft::parseEnv($settings->secretKey),
            'response' => $recaptchaResponse,
            'remoteip' => $request->getUserIP(),
        ];
        try {
            Craft::debug("Checking reCAPTCHA response", __METHOD__);
            $response = $client->request( 'POST', 'siteverify', [
                'form_params' => $params
            ]);
            if ($response->getStatusCode() == 200) {
                $result = Json::decodeIfJson($response->getBody());
            }
        } catch (ConnectException $e) {
            Craft::warning($e->getMessage(), __METHOD__);
            return false;
        }

        if (empty($result['success']) || (!empty($result['action']) && $result['action'] != "homepage")) {
            Craft::warning("reCAPTCHA check failed", __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * @return Client
     * @codeCoverageIgnore
     */
    public function getRecaptchaClient(): Client
    {
        $client = new Client([
            'base_uri' => 'https://www.google.com/recaptcha/api/',
            'timeout' => 5,
            'connect_timeout' => 5,
        ]);
        return $client;
    }
}

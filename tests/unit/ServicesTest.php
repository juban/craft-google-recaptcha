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
use craft\helpers\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\RequestInterface;
use simplonprod\googlerecaptcha\GoogleRecaptcha;
use simplonprod\googlerecaptcha\services\Recaptcha;
use UnitTester;
use yii\web\ForbiddenHttpException;

/**
 * @author    Simplon.Prod
 * @package   Google reCAPTCHA
 * @since     1.0.0
 */
class ServicesTest extends BaseUnitTest
{
    public const ACTION_HOMEPAGE = 'homepage';
    /**
     * @var UnitTester
     */
    protected $tester;

    protected $clientMock;

    public function _before()
    {
        parent::_before();
        Craft::$app->request->setBodyParams([
            'g-recaptcha-response' => 'some-response',
            'g-recaptcha-action' => Craft::$app->getSecurity()->hashData(self::ACTION_HOMEPAGE),
        ]);
    }

    public function testSuccessfulVerify(): void
    {
        $response = $this->make(Response::class,
            [
                'getStatusCode' => 200,
                'getBody' => Utils::streamFor(Json::encode([
                    'success' => true,
                    'action' => self::ACTION_HOMEPAGE,
                ])),
            ]);
        $googleRecaptchaService = $this->make(Recaptcha::class, [
            'getRecaptchaClient' => $this->_getClientMock($response),
        ]);
        $this->assertTrue($googleRecaptchaService->verify());
    }

    private function _getClientMock(MockObject $response)
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'siteverify', [
                'form_params' => [
                    'secret' => 'some-secret-key',
                    'response' => Craft::$app->request->getBodyParam('g-recaptcha-response'),
                    'remoteip' => Craft::$app->request->getUserIP(),
                ],
            ])
            ->willReturn($response);
        return $clientMock;
    }

    public function testFailedVerify(): void
    {
        $response = $this->make(Response::class,
            [
                'getStatusCode' => 200,
                'getBody' => Utils::streamFor(Json::encode([
                    'success' => false,
                ])),
            ]);
        $googleRecaptchaService = $this->make(Recaptcha::class, [
            'getRecaptchaClient' => $this->_getClientMock($response),
        ]);
        $this->assertFalse($googleRecaptchaService->verify());
    }

    public function testFailedVerifyWithWrongAction(): void
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 3,
            'actionName' => 'homepage',
        ]);
        $response = $this->make(Response::class,
            [
                'getStatusCode' => 200,
                'getBody' => Utils::streamFor(Json::encode([
                    'success' => true,
                    'action' => 'newsletter',
                ])),
            ]);
        $googleRecaptchaService = $this->make(Recaptcha::class, [
            'getRecaptchaClient' => $this->_getClientMock($response),
        ]);
        $this->assertFalse($googleRecaptchaService->verify());
    }

    public function testSuccessfulVerifyWithScore(): void
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 3,
            'scoreThreshold' => 0.5,
        ]);
        $response = $this->make(Response::class,
            [
                'getStatusCode' => 200,
                'getBody' => Utils::streamFor(Json::encode([
                    'success' => true,
                    'score' => 0.9,
                ])),
            ]);
        $googleRecaptchaService = $this->make(Recaptcha::class, [
            'getRecaptchaClient' => $this->_getClientMock($response),
        ]);
        $this->assertTrue($googleRecaptchaService->verify());
    }

    public function testSuccessfulVerifyWithScorePerAction(): void
    {
        \Craft::$app->request->setBodyParams([
            'g-recaptcha-response' => 'some-response',
            'g-recaptcha-action' => Craft::$app->getSecurity()->hashData('some_action'),
        ]);
        $response = $this->make(Response::class,
            [
                'getStatusCode' => 200,
                'getBody' => Utils::streamFor(Json::encode([
                    'success' => true,
                    'action' => 'some_action',
                    'score' => 0.9,
                ])),
            ]);
        $googleRecaptchaService = $this->make(Recaptcha::class, [
            'getRecaptchaClient' => $this->_getClientMock($response),
        ]);
        $this->assertTrue($googleRecaptchaService->verify());
    }

    public function testFailedVerifyWithScorePerAction(): void
    {
        \Craft::$app->request->setBodyParams([
            'g-recaptcha-response' => 'some-response',
            'g-recaptcha-action' => Craft::$app->getSecurity()->hashData('some_action'),
        ]);
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 3,
            'actions' => [
                ['name' => 'some_action', 'scoreThreshold' => 0.8],
            ],
        ]);
        $response = $this->make(Response::class,
            [
                'getStatusCode' => 200,
                'getBody' => Utils::streamFor(Json::encode([
                    'success' => true,
                    'action' => 'some_action',
                    'score' => 0.5,
                ])),
            ]);
        $googleRecaptchaService = $this->make(Recaptcha::class, [
            'getRecaptchaClient' => $this->_getClientMock($response),
        ]);
        $this->assertFalse($googleRecaptchaService->verify());
    }

    public function testFailedVerifyWithScore(): void
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 3,
            'scoreThreshold' => 0.9,
        ]);
        $response = $this->make(Response::class,
            [
                'getStatusCode' => 200,
                'getBody' => Utils::streamFor(Json::encode([
                    'success' => true,
                    'action' => 'homepage',
                    'score' => 0.5,
                ])),
            ]);
        $googleRecaptchaService = $this->make(Recaptcha::class, [
            'getRecaptchaClient' => $this->_getClientMock($response),
        ]);
        $this->assertFalse($googleRecaptchaService->verify());
    }

    public function testVerifyWithConnectException(): void
    {
        $response = $this->make(Response::class,
            [
                'getStatusCode' => 200,
                'getBody' => Json::encode([
                    'success' => false,
                ]),
            ]);
        $clientMock = $this->createMock(Client::class);
        $clientMock
            ->expects($this->once())
            ->method('request')
            ->will($this->throwException(new ConnectException('Something went wrong', $this->makeEmpty(RequestInterface::class))));
        $googleRecaptchaService = $this->make(Recaptcha::class, [
            'getRecaptchaClient' => $clientMock,
        ]);
        $this->assertFalse($googleRecaptchaService->verify());
    }

    public function testVerifyWithInvalidResponseException(): void
    {
        Craft::$app->request->setBodyParams([]);
        $this->expectException(ForbiddenHttpException::class);
        GoogleRecaptcha::$plugin->recaptcha->verify();
    }
}

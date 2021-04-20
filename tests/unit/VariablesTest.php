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

use simplonprod\googlerecaptcha\GoogleRecaptcha;
use simplonprod\googlerecaptcha\variables\GoogleRecaptchaVariable;
use UnitTester;

/**
 * @author    Simplon.Prod
 * @package   Google reCAPTCHA
 * @since     1.0.0
 */
class VariablesTest extends BaseUnitTest
{
    public const V2_OUTPUT_PATTERN = '/<div id="([\w-]+?)"><\/div>\s+<script type="text\/javascript">\s+var (\w+?) = function\(\) {\s+var widgetId = grecaptcha.render\("([\w-]+?)", {\s+(.*?)\s+}\);\s+(.*?)?};\s+(.*?\s+)?<\/script>\s+<script src="https:\/\/www.google.com\/recaptcha\/api\.js\?onload=(\w+?)&render=explicit&hl=([\w-]+?)" async defer><\/script>/s';
    public const V3_OUTPUT_PATTERN = '/<script src="https:\/\/www.google.com\/recaptcha\/api\.js\?render=([\w-]+?)"><\/script>\s+<script>\s+grecaptcha.ready\(function\(\) {\s+grecaptcha\.execute\("([\w-]+?)", {\s+action\: "(\w+?)"\s+}\)\.then\(function\(token\) {\s+document\.getElementById\("([\w-]+?)"\)\.value = token;\s+}\);\s+}\);\s+<\/script>/s';
    /**
     * @var UnitTester
     */
    protected $tester;

    protected $variable;

    public function _before()
    {
        parent::_before();
        $this->variable = new GoogleRecaptchaVariable();
    }

    /**
     *
     */
    public function testRenderV2NormalWithoutParams(): void
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version'   => 2,
            'siteKey'   => 'some-site-key',
            'secretKey' => 'some-secret-key',
            'size'      => 'normal',
            'theme'     => 'light',
            'badge'     => 'bottomright'
        ]);
        $output = $this->variable->render();
        $isValid = (bool)preg_match(self::V2_OUTPUT_PATTERN, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertEquals($matches[1], $matches[3]);
        $this->assertStringContainsString('sitekey: "some-site-key"', $matches[4]);
        $this->assertStringContainsString('size: "normal"', $matches[4]);
        $this->assertStringContainsString('theme: "light"', $matches[4]);
        $this->assertStringContainsString('badge: "bottomright"', $matches[4]);
        $this->assertEquals('', $matches[5]);
        $this->assertNotEmpty($matches);
    }

    public function testRenderV2NormalWithParams()
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version'   => 2,
            'siteKey'   => 'some-site-key',
            'secretKey' => 'some-secret-key',
            'size'      => 'normal',
            'theme'     => 'light',
            'badge'     => 'bottomright'
        ]);
        $output = $this->variable->render(['id' => 'my-recaptcha']);
        $isValid = (bool)preg_match(self::V2_OUTPUT_PATTERN, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertEquals('my-recaptcha', $matches[1]);
        $this->assertEquals('myRecaptcha', $matches[2]);
        $this->assertEquals('my-recaptcha', $matches[3]);
        $this->assertEquals('', $matches[5]);
        $this->assertEquals('myRecaptcha', $matches[7]);
        $this->assertEquals('en-US', $matches[8]);
    }

    public function testRenderV2InvisibleWithParams()
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version'   => 2,
            'siteKey'   => 'some-site-key',
            'secretKey' => 'some-secret-key',
            'size'      => 'invisible',
            'theme'     => 'light',
            'badge'     => 'bottomright'
        ]);
        $output = $this->variable->render(['id' => 'my-recaptcha']);
        $isValid = (bool)preg_match(self::V2_OUTPUT_PATTERN, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertEquals('my-recaptcha', $matches[1]);
        $this->assertEquals('myRecaptcha', $matches[2]);
        $this->assertEquals('my-recaptcha', $matches[3]);
        $this->assertStringContainsString('size: "invisible"', $matches[4]);
        $this->assertStringContainsString('grecaptcha.execute(widgetId);', $matches[5]);
        $this->assertEquals('myRecaptcha', $matches[7]);
        $this->assertEquals('en-US', $matches[8]);
    }

    public function testRenderV2CompactInstant()
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version'   => 2,
            'siteKey'   => 'some-site-key',
            'secretKey' => 'some-secret-key',
            'size'      => 'compact',
            'theme'     => 'light',
            'badge'     => 'bottomright'
        ]);
        $output = $this->variable->render(['id' => 'my-recaptcha'], true);
        $isValid = (bool)preg_match(self::V2_OUTPUT_PATTERN, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertEquals('my-recaptcha', $matches[1]);
        $this->assertEquals('myRecaptcha', $matches[2]);
        $this->assertEquals('my-recaptcha', $matches[3]);
        $this->assertStringContainsString('size: "compact"', $matches[4]);
        $this->assertEquals('', $matches[5]);
        $this->assertStringContainsString('myRecaptcha();', $matches[6]);
        $this->assertEquals('myRecaptcha', $matches[7]);
        $this->assertEquals('en-US', $matches[8]);
    }

    public function testRenderV3WithoutParams(): void
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version'   => 3,
            'siteKey'   => 'some-site-key',
            'secretKey' => 'some-secret-key'
        ]);
        $output = $this->variable->render();
        $isValid = (bool)preg_match(self::V3_OUTPUT_PATTERN, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertStringContainsString('some-site-key', $matches[1]);
        $this->assertStringContainsString('some-site-key', $matches[2]);
        $this->assertStringContainsString('homepage', $matches[3]);
    }

    public function testRenderV3WithParams()
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version'   => 3,
            'siteKey'   => 'some-site-key',
            'secretKey' => 'some-secret-key'
        ]);
        $output = $this->variable->render(['id' => 'my-recaptcha']);
        $isValid = (bool)preg_match(self::V3_OUTPUT_PATTERN, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertStringContainsString('my-recaptcha', $matches[4]);
    }
}

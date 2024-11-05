<?php
/**
 * Newsletter plugin for Craft CMS 3.x
 *
 * Craft CMS Newsletter plugin
 *
 * @link      https://github.com/juban
 * @copyright Copyright (c) 2022 juban
 */

namespace juban\googlerecaptchatests\unit;

use juban\googlerecaptcha\GoogleRecaptcha;
use juban\googlerecaptcha\variables\GoogleRecaptchaVariable;
use UnitTester;

/**
 * @author    juban
 * @package   Google reCAPTCHA
 * @since     1.0.0
 */
class VariablesTest extends BaseUnitTest
{
    public const V2_OUTPUT_PATTERN = '/<div id="([\w-]+?)"><\/div>\s+<script type="text\/javascript"((\s+\w+?(=".+?")?)*)?>\s+var (\w+?) = function\(\) {\s+var widgetId = grecaptcha.render\("([\w-]+?)", {\s+(.*?)\s+}\);\s+(.*?)?};\s+(.*?\s+)?<\/script>\s+<script src="https:\/\/www.google.com\/recaptcha\/api\.js\?onload=(\w+?)&render=explicit&hl=([\w-]+?)" async defer((\s+\w+?(=".+?")?)*)?><\/script>/s';
    public const V3_OUTPUT_PATTERN_SIMPLE = '/<script src="https:\/\/www.google.com\/recaptcha\/api\.js\?render=([\w-]+?)"((\s+\w+?(=".+?")?)*)?><\/script>\s+<script((\s+\w+?(=".+?")?)*)?>\s+grecaptcha.ready\(function\(\) {\s+grecaptcha\.execute\("([\w-]+?)", {\s+action\: "(\w+?)"\s+}\)\.then\(function\(token\) {\s+document\.getElementById\("([\w-]+?)"\)\.value = token;\s+}\);\s+}\);\s+<\/script>/s';
    public const V3_OUTPUT_PATTERN_WITH_FORMID = '/<script src="https:\/\/www.google.com\/recaptcha\/api\.js\?render=([\w-]+?)"((\s+\w+?(=".+?")?)*)?><\/script>\s+<script((\s+\w+?(=".+?")?)*)?>\s+grecaptcha.ready\(function\(\) {\s+document\.getElementById\("(.*?)\"\)\.addEventListener\("submit\"\,\s+function\(event\)\s+{\s+event\.preventDefault\(\);\s+grecaptcha\.execute\(\"(.*?)\",\s+{\s+action:\s+"(.*?)\"\s+}\)\.then\(function\(token\)\s+{\s+document\.getElementById\(\"(.*?)\"\)\.value\s+=\s+token;\s+document\.getElementById\(\"(.*?)\"\)\.submit\(\);\s+}\);\s+},\s+false\);\s+}\);\s+<\/script>/s';
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
            'version' => 2,
            'siteKey' => 'some-site-key',
            'secretKey' => 'some-secret-key',
            'size' => 'normal',
            'theme' => 'light',
            'badge' => 'bottomright',
        ]);
        $output = $this->variable->render();
        $isValid = (bool)preg_match(self::V2_OUTPUT_PATTERN, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertEquals($matches[1], $matches[6]);
        $this->assertStringContainsString('sitekey: "some-site-key"', $matches[7]);
        $this->assertStringContainsString('size: "normal"', $matches[7]);
        $this->assertStringContainsString('theme: "light"', $matches[7]);
        $this->assertStringContainsString('badge: "bottomright"', $matches[7]);
        $this->assertEquals('', $matches[8]);
        $this->assertNotEmpty($matches);
    }

    public function testRenderV2NormalWithParams()
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 2,
            'siteKey' => 'some-site-key',
            'secretKey' => 'some-secret-key',
            'size' => 'normal',
            'theme' => 'light',
            'badge' => 'bottomright',
        ]);
        $output = $this->variable->render(['id' => 'my-recaptcha']);
        $isValid = (bool)preg_match(self::V2_OUTPUT_PATTERN, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertEquals('my-recaptcha', $matches[1]);
        $this->assertEquals('', $matches[2]);
        $this->assertEquals('', $matches[3]);
        $this->assertEquals('myRecaptcha', $matches[5]);
        $this->assertEquals('my-recaptcha', $matches[6]);
        $this->assertEquals('', $matches[8]);
        $this->assertEquals('myRecaptcha', $matches[10]);
        $this->assertEquals('en-US', $matches[11]);
        $this->assertEquals('', $matches[12]);
    }

    public function testRenderV2InvisibleWithParams()
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 2,
            'siteKey' => 'some-site-key',
            'secretKey' => 'some-secret-key',
            'size' => 'invisible',
            'theme' => 'light',
            'badge' => 'bottomright',
        ]);
        $output = $this->variable->render(['id' => 'my-recaptcha']);
        $isValid = (bool)preg_match(self::V2_OUTPUT_PATTERN, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertEquals('my-recaptcha', $matches[1]);
        $this->assertEquals('myRecaptcha', $matches[5]);
        $this->assertEquals('my-recaptcha', $matches[6]);
        $this->assertStringContainsString('size: "invisible"', $matches[7]);
        $this->assertStringContainsString('grecaptcha.execute(widgetId);', $matches[8]);
        $this->assertEquals('myRecaptcha', $matches[10]);
        $this->assertEquals('en-US', $matches[11]);
    }

    public function testRenderV2CompactInstant()
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 2,
            'siteKey' => 'some-site-key',
            'secretKey' => 'some-secret-key',
            'size' => 'compact',
            'theme' => 'light',
            'badge' => 'bottomright',
        ]);
        $output = $this->variable->render(['id' => 'my-recaptcha'], true);
        $isValid = (bool)preg_match(self::V2_OUTPUT_PATTERN, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertEquals('my-recaptcha', $matches[1]);
        $this->assertEquals('myRecaptcha', $matches[5]);
        $this->assertEquals('my-recaptcha', $matches[6]);
        $this->assertStringContainsString('size: "compact"', $matches[7]);
        $this->assertEquals('', $matches[8]);
        $this->assertStringContainsString('myRecaptcha();', $matches[9]);
        $this->assertEquals('myRecaptcha', $matches[10]);
        $this->assertEquals('en-US', $matches[11]);
    }

    public function testRenderV2NormalWithScriptOptions()
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 2,
            'siteKey' => 'some-site-key',
            'secretKey' => 'some-secret-key',
            'size' => 'normal',
            'theme' => 'light',
            'badge' => 'bottomright',
        ]);
        $output = $this->variable->render(['id' => 'my-recaptcha', 'scriptOptions' => ['nonce' => 'a123456']]);
        $isValid = (bool)preg_match(self::V2_OUTPUT_PATTERN, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertEquals('my-recaptcha', $matches[1]);
        $this->assertStringContainsString('nonce="a123456"', $matches[2]);
        $this->assertStringContainsString('nonce="a123456"', $matches[3]);
        $this->assertEquals('myRecaptcha', $matches[5]);
        $this->assertEquals('my-recaptcha', $matches[6]);
        $this->assertEquals('', $matches[8]);
        $this->assertEquals('myRecaptcha', $matches[10]);
        $this->assertEquals('en-US', $matches[11]);
        $this->assertStringContainsString('nonce="a123456"', $matches[12]);
        $this->assertStringContainsString('nonce="a123456"', $matches[13]);
    }

    public function testRenderV3WithoutParams(): void
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 3,
            'siteKey' => 'some-site-key',
            'secretKey' => 'some-secret-key',
        ]);
        $output = $this->variable->render();
        $isValid = (bool)preg_match(self::V3_OUTPUT_PATTERN_SIMPLE, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertEquals($matches[1], $matches[8]);
        $this->assertStringContainsString('some-site-key', $matches[1]);
        $this->assertEquals('', $matches[2]);
        $this->assertEquals('', $matches[3]);
        $this->assertEquals('', $matches[5]);
        $this->assertEquals('', $matches[6]);
        $this->assertStringContainsString('some-site-key', $matches[8]);
        $this->assertStringContainsString('homepage', $matches[9]);
    }

    public function testRenderV3WithParams()
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 3,
            'siteKey' => 'some-site-key',
            'secretKey' => 'some-secret-key',
        ]);
        $output = $this->variable->render(['id' => 'my-recaptcha']);
        $isValid = (bool)preg_match(self::V3_OUTPUT_PATTERN_SIMPLE, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertStringContainsString('homepage', $matches[9]);
        $this->assertStringContainsString('my-recaptcha', $matches[10]);
    }

    public function testRenderV3WithScriptOptions(): void
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 3,
            'siteKey' => 'some-site-key',
            'secretKey' => 'some-secret-key',
        ]);
        $output = $this->variable->render(['scriptOptions' => ['nonce' => '123456Z']]);
        $isValid = (bool)preg_match(self::V3_OUTPUT_PATTERN_SIMPLE, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertEquals($matches[1], $matches[8]);
        $this->assertStringContainsString('some-site-key', $matches[1]);
        $this->assertStringContainsString('nonce="123456Z"', $matches[2]);
        $this->assertStringContainsString('nonce="123456Z"', $matches[3]);
        $this->assertStringContainsString('nonce="123456Z"', $matches[5]);
        $this->assertStringContainsString('nonce="123456Z"', $matches[6]);
        $this->assertStringContainsString('some-site-key', $matches[8]);
        $this->assertStringContainsString('homepage', $matches[9]);
    }

    public function testRenderV3WithFormIdOptions(): void
    {
        GoogleRecaptcha::$plugin->setSettings([
            'version' => 3,
            'siteKey' => 'some-site-key',
            'secretKey' => 'some-secret-key',
        ]);
        $output = $this->variable->render(['formId' => 'some-form-id', 'scriptOptions' => ['nonce' => '123456Z']]);
        $isValid = (bool)preg_match(self::V3_OUTPUT_PATTERN_WITH_FORMID, $output, $matches);
        $this->assertTrue($isValid);
        $this->assertStringContainsString('some-form-id', $matches[8]);
        $this->assertStringContainsString('nonce="123456Z"', $matches[2]);
        $this->assertEquals($matches[8], $matches[12]);
        $this->assertEquals($matches[2], $matches[3]);
    }
}

<?php

class SettingsTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $settings;

    public function testV3ActionsValidationSuccess()
    {
        $this->settings->version = 3;
        $this->settings->actions = [
            [
                'name' => 'some_action',
                'scoreThreshold' => 0.5,
            ],
        ];
        $this->assertTrue($this->settings->validate());
    }

    public function testV3ActionsValidationFailure()
    {
        $this->settings->version = 3;
        $this->settings->actions = [
            [
                'name' => 'some_action',
                'scoreThreshold' => 0.5,
            ],
            [
                'name' => 'other action',
                'scoreThreshold' => '',
            ],
        ];
        $this->assertFalse($this->settings->validate());
        $this->assertTrue($this->settings->actions[1]->hasErrors());
    }

    protected function _before()
    {
        $this->settings = new \simplonprod\googlerecaptcha\models\Settings();
        $this->settings->secretKey = 'secretkey';
        $this->settings->siteKey = 'sitekey';
    }

    // tests

    protected function _after()
    {
    }
}

<?php
/**
 * @link https://github.com/juban
 * @copyright Copyright (c) 2023 juban
 */

namespace juban\googlerecaptcha\events;

use craft\events\CancelableEvent;

class BeforeRecaptchaVerifyEvent extends CancelableEvent
{
    public $skipVerification = false;
}

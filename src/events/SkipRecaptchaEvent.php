<?php

namespace juban\googlerecaptcha\events;

use yii\base\Event;

class SkipRecaptchaEvent extends Event
{
    /**
     * Should skip verification
     *
     * @var bool
     */
    public bool $skipVerification = false;
}
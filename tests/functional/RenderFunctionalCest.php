<?php

namespace simplonprod\googlerecaptchatests\acceptance;

use FunctionalTester;

class RenderFunctionalCest
{
    /**
     *
     */
    public function renderRecaptcha(FunctionalTester $I)
    {
        $I->amOnPage('?p=render');
        $I->seeResponseCodeIs(200);
        $I->seeElement('#recaptcha-test');
    }
}

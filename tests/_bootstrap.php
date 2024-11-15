<?php

use craft\test\TestSetup;

ini_set('date.timezone', 'UTC');
date_default_timezone_set('UTC');

// Use the current installation of Craft
define('CRAFT_ROOT_PATH', dirname(__DIR__));
define('CRAFT_TESTS_PATH', __DIR__);
define('CRAFT_STORAGE_PATH', __DIR__ . '/_craft/storage');
define('CRAFT_TEMPLATES_PATH', __DIR__ . '/_craft/templates');
define('CRAFT_CONFIG_PATH', __DIR__ . '/_craft/config');
define('CRAFT_MIGRATIONS_PATH', __DIR__ . '/_craft/migrations');
define('CRAFT_TRANSLATIONS_PATH', __DIR__ . '/_craft/translations');
define('CRAFT_VENDOR_PATH', dirname(__DIR__) . '/vendor');

// Set some fake env vars for functional tests purpose
putenv('RECAPTCHA_SITE_KEY=aazertyuiop');
putenv('RECAPTCHA_SECRET_KEY=aazertyuiop');

$devMode = true;

TestSetup::configureCraft();

// Set the @webroot alias so that the cpresources folder is created in the correct directory
Craft::setAlias('@webroot', __DIR__ . '/_craft/web');

// Prevent `headers already sent` error
ob_start();

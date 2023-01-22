# Google reCAPTCHA plugin for Craft CMS 4

[![Stable Version](https://img.shields.io/packagist/v/jub/craft-google-recaptcha?label=stable)]((https://packagist.org/packages/jub/craft-google-recaptcha))
[![Total Downloads](https://img.shields.io/packagist/dt/jub/craft-google-recaptcha)](https://packagist.org/packages/jub/craft-google-recaptcha)
![Tests status](https://github.com/juban/craft-google-recaptcha/actions/workflows/ci.yml/badge.svg?branch=master)

![](logo.png)



Google reCAPTCHA for Craft CMS enables to render and validate the reCAPTCHA widget. It is compatible with both API v2 and v3, including checkbox badge and invisible flavors.

## Requirements

This plugin requires Craft CMS 4.0.0 or later and PHP 8.0.2 or later.

To use the plugin, you will need to get an API site key and secret key which you can configure [here](https://www.google.com/recaptcha/admin).

## Installation

1. Install with composer via `composer require jub/craft-google-recaptcha` from your project directory or from the Plugin Store section of the Control Panel.
2. Install the plugin in the Craft Control Panel under Settings → Plugins, or from the command line via `./craft plugin/install google-recaptcha`.


## Configuration

### Control Panel

You can manage configuration setting through the Control Panel by going to Settings → Google reCAPTCHA

* Provide the Site Key and the Secret Key obtained from [your reCAPTCHA account](https://www.google.com/recaptcha/admin).
* Select the API version accordingly to the reCAPTCHA type you created the keys upon.
* For v2 API, select the following parameters: 
	* Size: Select the size of the reCAPTCHA widget. 
	* Theme: Select the color theme of the reCAPTCHA widget.
	* Badge: Select the position of the reCAPTCHA badge.
* For v3 API: 
	* Default Action: the default action name to be used during reCAPTCHA verification. Defaults to `homepage` if blank.
	* Default Score Threshold : Minimum score between 0 and 1 to obtain in order for the end user to validate the reCAPTHCHA challenge (see [here](https://developers.google.com/recaptcha/docs/v3#interpreting_the_score) for help on interpreting the score)
. Leave blank for no score checking. 	
	* Actions: A score threshold can be defined per action here.

### Configuration file

You can create a `google-recaptcha.php` file in the `config` folder of your project and provide the settings as follow:

```php
return [
    "version"   		=> 2, // Either 2 our 3
    "siteKey"   		=> '', // Site key
    "secretKey" 		=> '', // Secret key
    "size"      		=> 'normal', // (v2) normal, compact or invisible
    "theme"     		=> 'light', // (v2) light or dark
    "badge"     		=> 'bottomright', // (v2) bottomright, bottomleft or inline
    "actionName"        => 'homepage', // (v3) Default action name
    "scoreThreshold"	=> 0.5 // (v3) Value between 0 and 1 to determine the minimum score to validate
    "actions"			=> [ // (v3) List of actions with their associated score threshold value (see the template part below to know how to specify the action parameter in the render method)
    	[
    		'name' 				=> 'some_action_name',
    		'scoreThreshold' 	=> 0.5
    	]
    ]
    
];
```

> ⚠️ Any value provided in that file will override the settings from the Control Panel.

## Using Google reCAPTCHA

### Display Google reCAPTCHA widget in template

You can integrate the Google reCAPTCHA widget in your Twig templates as follow:

```twig
{{ craft.googleRecaptcha.render() }}
```

The `render` method accept an optional parameter in which you can provide any HTML attributes to apply to the widget container (div for v2, hidden input for v3).  
For example, to provide a container id, you can do:


```twig
{{ craft.googleRecaptcha.render({id: 'recaptcha-widget'}) }}
```

For v3 API, an action property can also be provided as follow:

```twig
{{ craft.googleRecaptcha.render({id: 'recaptcha-widget', action: 'some_action_name'}) }}
```

In that case, the score threshold to be used for that action can be defined in the "Actions Settings" part of the plugin control panel.

> 💡 For v2 API, you can provide a second boolean argument to the render method to trigger the instant rendering of the widget (ie. `{{ craft.googleRecaptcha.render({ id: 'recaptcha-widget' }, true) }}`). 
> This is useful if you are working with views loaded through Ajax or [Sprig](https://plugins.craftcms.com/sprig) calls and you need to refresh the widget.

#### Setting scripts extra attributes

In the first render parameter, `scriptOptions` special property can be used to add extra attributes to the generated scripts tags.

For example, to support Content Security Policy (CSP), assuming you are using the [Sherlock](https://plugins.craftcms.com/sherlock) security plugin, you could do the following:

```twig
{% set nonce = craft.sherlock.getNonce() %}
{{ craft.googleRecaptcha.render({scriptOptions: {'nonce': nonce}}) }}
```


### Verify users submissions

To validate a user submission on server side, you can use the built-in method:

```php
GoogleRecaptcha::$plugin->recaptcha->verify();
```

For example, in a module controller, you could do something like this:

```php
public function actionSubmitForm() {
	if(GoogleRecaptcha::$plugin->recaptcha->verify()) {
		// Do something useful here
	}
	else {
		Craft::$app->session->setError('Looks like you are a robot!');
	}

}
```

### Verify Guest Entries submissions

In order to add a reCAPTCHA verification when working with [Craft Guest Entries plugin](https://plugins.craftcms.com/guest-entries), you can do something like the following in a project module:

```php
Event::on(SaveController::class, SaveController::EVENT_BEFORE_SAVE_ENTRY, function (SaveEvent $e) {
    /** @var Entry $submission */
    $submission = $e->entry;
    $submission->setScenario(Element::SCENARIO_LIVE);
    $submission->validate();
    // Check reCAPTCHA
    $isValid = GoogleRecaptcha::$plugin->recaptcha->verify();
    if (!$isValid) {
        $submission->addError('recaptcha', 'Please, prove you’re not a robot.');
        $e->isValid = false;
    }
});
```

### Verify Contact Form submissions

In order to add a reCAPTCHA verification when working with [Contact Form](https://plugins.craftcms.com/contact-form), you can do something like the following in a project module:

```php
Event::on(Submission::class, Submission::EVENT_AFTER_VALIDATE, function(Event $e) {
    /** @var Submission $submission */
    $submission = $e->sender;
    // Check reCAPTCHA
    $isValid = GoogleRecaptcha::$plugin->recaptcha->verify();
    if (!$isValid) {
        $submission->addError('recaptcha', 'Please, prove you’re not a robot.');
    }
});

```

### Available events

* The `\juban\googlerecaptcha\events\BeforeRecaptchaVerifyEvent` event is triggered just before a reCAPTCHA verification is performed. You can use that event to:
	* **Bypass the verification** by setting the `skipVerification` event property to `true`. In that case, verification will be considered as successful.
	* **Cancel the verification** by setting the `isValid` event property to `false`. In that case, verification will be considered as failed).

---

# Google reCAPTCHA plugin for Craft CMS 3

![](logo.png)

Google reCAPTCHA for Craft CMS enables to render and validate the reCAPTCHA widget. It is compatible with both API v2 and v3, including checkbox badge and invisible flavors.

## Requirements

This plugin requires Craft CMS 3.7.29 or later and PHP 7.2.5 or later.

To use the plugin, you will need to get an API site key and secret key which you can configure [here](https://www.google.com/recaptcha/admin).

## Installation

1. Install with composer via `composer require simplonprod/craft-google-recaptcha` from your project directory or from the Plugin Store section of the Control Panel.
2. Install the plugin in the Craft Control Panel under Settings → Plugins, or from the command line via `./craft plugin/install google-recaptcha`.


## Configuration

### Control Panel

You can manage configuration setting through the Control Panel by going to Settings → Google reCAPTCHA

* Provide the Site Key and the Secret Key obtained from [your reCAPTCHA account](https://www.google.com/recaptcha/admin).
* Select the API version accordingly to the reCAPTCHA type you created the keys upon.
* For v2 API, select desired look and feel for Theme, Size and Badge parameters.

### Configuration file

You can create a `google-recaptcha.php` file in the `config` folder of your project and provide the settings as follow:

```php
return [
    "version"   => 2, // Either 2 our 3
    "siteKey"   => '', // Site key
    "secretKey" => '', // Secret key
    "size"      => 'normal', // normal, compact or invisible
    "theme"     => 'light', // light or dark
    "badge"     => 'bottomright' // bottomright, bottomleft or inline
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
{{ craft.googleRecaptcha.render({ id: 'recaptcha-widget' }) }}
```

> 💡 For API v2, you can provide a second boolean argument to the render method to trigger the instant rendering of the widget (ie. `{{ craft.googleRecaptcha.render({ id: 'recaptcha-widget' }, true) }}`).  
> This is useful if you are working with views loaded through Ajax or [Sprig](https://plugins.craftcms.com/sprig) calls and you need to refresh the widget.

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

---

<small>Created by [Simplon.Prod](https://www.simplonprod.co/).</small>


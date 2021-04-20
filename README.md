# Google reCAPTCHA plugin for Craft CMS 3

Google reCAPTCHA for Craft CMS enable to render and validate the reCAPTCHA widget. It is compatible with both API v2 and v3, including checkbox badge and invisible flavors.

## Requirements

This plugin requires Craft CMS 3.2.0 or later and PHP 7.2.5 or later.

## Installation

1. Install with composer via `composer require simplonprod/craft-google-recaptcha` from your project directory.
2. Install the plugin in the Craft Control Panel under Settings → Plugins, or from the command line via `./craft plugin/install google-recaptcha`.
3. Select and configure the service under Settings → Google reCAPTCHA

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

> For API v2, you can provide a second boolean argument to the render method to trigger the instant rendering of the widget (ie. `{{ craft.googleRecaptcha.render({ id: 'recaptcha-widget' }, true) }}`).  
> This is useful if you are working with views loaded through Ajax or Sprig calls and you need to refresh the widget.

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


---

<small>Created by [Simplon.Prod](https://www.simplonprod.co/).</small>


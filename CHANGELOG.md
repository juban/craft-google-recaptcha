# Google Recaptcha Changelog

## 2.1.0 - 2022-07-23

> {note} The plugin’s package name has changed to `jub/craft-google-recaptcha`. You can update the plugin by running `composer require jub/craft-google-recaptcha && composer remove simplonprod/craft-google-recaptcha`.

# Changed
- Migrate plugin to `jub/craft-google-recaptcha`
- Updated plugin logo


## 2.0.2 - 2022-05-13
### Fixed
- Fix an exception that could occur in verify method if no actions parameters were saved (merged from 1.1.1)

## 2.0.1 - 2022-05-09

### Fixed
- reCAPTCHA v3 actions parameters were missing from the control panel

## 2.0.0 - 2022-05-08

### Added
- Added Craft 4 compatibility.

## 1.1.1 - 2022-05-13
### Fixed
- Fix an exception that could occur in verify method if no actions parameters were saved.

## 1.1.0 - 2022-03-07
### Added
- (v3 API) Default action name and score threshold can be configured
- (v3 API) Score threshold can be defined per action
- (v3 API) Ability to specify the action name in the twig `craft.googleRecaptcha.render()` function first parameter.

### Changed
- Google reCAPTCHA plugin options can now be set using environment variables
- Bump minimum required Craft version to 3.7.29

## 1.0.4 - 2021-05-03
### Added
- Contact form instructions in README

### Changed
- Updated plugin icon

## 1.0.3 - 2021-05-01
### Changed
- Update docs, issues and changelog links in composer.json
- Upgrade codeception to v4
- Fix composer dependencies
- Settings view refinements
- Various small refactoring

## 1.0.2 - 2021-04-20
### Added
- Full unit and functional tests coverage

### Changed
- Templates to render v2 and v3 tags
- More robust settings validation rules

## 1.0.1 - 2021-04-07
### Added
- instantRender parameter to twig render method (for v2 ajax calls context)

## 1.0.0 - 2021-04-01
### Added
- Initial release

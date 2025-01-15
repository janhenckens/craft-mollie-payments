# Mollie Payments Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 5.2.3 - 2025-01-15
### Fixed
- Fixed an error when viewing payment details

## 5.2.2 - 2025-01-11
### Fixed
- Fixed status check links for Subscription elements
- Subscriptions now pass the correct start date to Mollie

## 5.2.1 - 2025-01-10
### Fixed
- Added missing form handle

## 5.2.0 - 2025-01-04
### Added
- Support for using a different API key per form
### Fixed
- Fixed breadcrumbs on the payment edit screen

## 5.1.0 - 2024-08-26
### Added
- Full support for Mollie Subscriptions ([docs can be found here](https://studioespresso.github.io/craft-mollie-payments/subscription-getting-started.html))

## 5.1.0-beta.3 - 2024-07-29
### Added
- Added an overview for subscribers

### Fixed
- Fixed an error when canceling a subscription from the CP

## 5.1.0-beta.2 - 2024-07-07
### Fixed
- Fix for payments that exist before upgrading to 5.1


## 5.1.0-beta.1 - 2024-07-04
### Added
- Initial support for Mollie Subscriptions

## 5.0.0 - 2024-02-21
### Added
- Full Craft 5 support
- Dutch translations
- French translations

## 5.0.0-alpha.1 - 2023-12-20
### Added
- Initial Craft 5 support

## 3.1.3 - 2023-03-10
### Fixed
- Fixed a missing namespace

## 3.1.2 - 2023-03-04
### Fixed
- Fixed an error on the settings screen when API keys were set to an array in the config file.


## 3.1.1 - 2023-01-19
### Fixed
- Payment elements hav links again on the overview [#61](https://github.com/studioespresso/craft-mollie-payments/issues/61)

## 3.1.0 - 2022-12-29
### Added
- We now pass a ``currentSite`` parameter in the metadata of all payments [#59](https://github.com/studioespresso/craft-mollie-payments/issues/59)
- Basic support for added custom metadata when creating your own payments


## 3.0.0 - 2022-05-02
### Added
- Craft 4 🚀

## 3.0.0-beta.1 - 2022-03-19
### Added
- Craft 4 compatibility


## 2.2.3 - 2022-03-13
### Fixed
- Fixed headers on transactions table


## 2.2.2 - 2022-03-13
### Added
- Added transaction property to payment to expose more detailed payment details (eg payment method)


## 2.2.1 - 2022-01-10
### Fixed
- Fixed validation for donation payments ([#44](https://github.com/studioespresso/craft-mollie-payments/pull/44), thanks [boboldehampsink](https://github.com/boboldehampsink))


## 2.2.0 - 2021-06-18
### Added
- Added support for Craft's new(ish) UI element in the forms field layout editor
- Redirects after payment are now verified for better security

## 2.1.4 - 2021-05-16
### Fixed
- Fixed an issue with donation form submits ([#31](https://github.com/studioespresso/craft-mollie-payments/issues/31))



## 2.1.3 - 2021-05-04
### Fixed
- Forms can now be save without fields ([#30](https://github.com/studioespresso/craft-mollie-payments/issues/30))
- Fixed an error deleting forms from project config ([#30](https://github.com/studioespresso/craft-mollie-payments/issues/30))


## 2.1.2 - 2021-04-20
### Fixed
- Fixed an issue with multi-step payments ([#29](https://github.com/studioespresso/craft-mollie-payments/issues/29))

## 2.1.1 - 2021-04-18
### Fixed
- Fixed an error where expired payments would in labelled as refundend.


## 2.1.0 - 2021-04-08
### Added
- Added better support for full refunds, payments and transactions now report the correcty status when a paymeny is refunded in Mollie


## 2.0.1 - 2021-03-13
### Fixed
- The plugin now properly supports project-config


## 2.0.0 - 2021-03-07
### Added
- The plugin now supports Craft's Project Config
- E-mail address has been added to the meta data sent to Mollie
- Requires Craft 3.5.0 or higher

## 1.6.4 - 2021-02-26
### Fixed
- Fixed an issue with querring payments by status ([#23](https://github.com/studioespresso/craft-mollie-payments/issues/23))

## 1.6.3 - 2021-02-15
### Fixed
- Fixed support for PostgreSQL (Thanks @boboldehampsink [#21](https://github.com/studioespresso/craft-mollie-payments/pull/21))


## 1.6.2 - 2020-12-19
### Fixed
- Fixed validation for custom fields


## 1.6.1 - 2020-10-13
### Fixed
- Proper validation for payment elements


## 1.6.0 - 2020-10-05
### Added
- The plugin now supports using a different API key per site. [#12](https://github.com/studioespresso/craft-mollie-payments/issues/12)

## 1.5.3 - 2020-10-05
### Added
- Added a statuc column to the all payments export

## 1.5.2 - 2020-04-15
### Fixed
- Fixed an issue with deleting payment forms

## 1.5.1 - 2020-04-01
### Fixed
- Fixed an issue the donate action when using a multi-step form 

## 1.5.0 - 2020-03-07
### Added
- Added a `donate` action so you can have forms where the user can choose the price. An example form can be found [here](https://studioespresso.github.io/craft-mollie-payments/template.html#donation-form) 

## 1.4.2 - 2020-03-04
### Fixed
- Fix an error where payment element wouldn't be assign an email address.

## 1.4.1 - 2020-03-02
### Fixed
- Payments with status "in cart" can now be viewed & clicked in the CP

## 1.4.0 - 2020-03-01
### Added
- Date created is now shown on element overviews ([#7](https://github.com/studioespresso/craft-mollie-payments/issues/7))
- Payments can now also be done in multi-step forms, using your own controller action. Docs can be found [here](https://studioespresso.github.io/craft-mollie-payments/template.html#multi-step-form)

## 1.3.0 - 2019-12-08
### Added
- Added ``craft.payments``, making it easy to display data from the payment element on confirmation & profile pages. ([#6](https://github.com/studioespresso/craft-mollie-payments/issues/6)) 

## 1.2.0 - 2019-12-04
### Added
- The payment description visible in Mollie can now be set per payment form and  can include values from the custom fields that are assigned ot the form. 

## 1.1.5 - 2019-11-30
### Fixed 
- Fixed a typo that caused the webhook post to crash

## 1.1.4 - 2019-11-28
### Fixed 
- Payment amounts can now be decimals and not just numbers

## 1.1.3 - 2019-11-27
### Fixed 
- Fixed an issue with conflicting variable names in the forms index template

## 1.1.2 - 2019-11-26
### Fixed 
- Fixed a missing variable when handling free payments

## 1.1.1 - 2019-11-25
### Fixed 
- Fixed the first attribute in the CP index view for pre Craft 3.2 installs

## 1.1.0 - 2019-11-25
### Added
- Payments for a zero amount can now be handled within the same flow as regular payments

### Fixed 
- Fixed `formId` attribute  in Payments query, fixing overview per forms in the CP

## 1.0.0 - 2019-11-24
### Added
- Added action to export selected payments to csv
- Added action to select all payments to csv

## 1.0.0-beta.2
### Added
- Payment elements can now be deleted from the overview
- Email is now the main UI label for each element in the overview
- `EVENT_BEFORE_PAYMENT_SAVE` event

### Fixed
- Payment records are now deleted when the element is deleted

## 1.0.0-beta.1
### Added
- Initial release
